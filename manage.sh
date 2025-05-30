#!/bin/bash
set -e

DB_FILE="./database/main_todos.sql"
DB_DIR="./database"
SCHEDULER_DIR="./docker/scheduler"
SRC_DIR="./src"

# Check if Docker is installed
function check_docker() {
  if ! command -v docker &> /dev/null; then
    echo "Error: Docker is not installed or not in PATH"
    echo "Please install Docker from https://docs.docker.com/get-docker/"
    exit 1
  fi
  
  if ! docker info &> /dev/null; then
    echo "Error: Docker daemon is not running or you don't have permission to access it"
    echo "Please start Docker and ensure your user has the necessary permissions"
    exit 1
  fi
}

# Ensure database directory and file exist
function ensure_db_file() {
  if [ ! -d "$DB_DIR" ]; then
    echo "Creating database directory..."
    mkdir -p "$DB_DIR"
  fi
  if [ ! -f "$DB_FILE" ]; then
    echo "Creating initial database file..."
    touch "$DB_FILE"
    echo "-- MariaDB init file created" > "$DB_FILE"
  fi
}

# Ensure scheduler files exist
function ensure_scheduler_files() {
  if [ ! -d "$SCHEDULER_DIR" ]; then
    echo "Creating scheduler directory..."
    mkdir -p "$SCHEDULER_DIR"
  fi
}

# Ensure Laravel .env file exists
function ensure_env_file() {
  if [ ! -f "$SRC_DIR/.env" ] && [ -f "$SRC_DIR/.env.example" ]; then
    echo "Creating .env file from example..."
    cp "$SRC_DIR/.env.example" "$SRC_DIR/.env"
    
    # Update database connection details in .env
    sed -i.bak 's/DB_HOST=.*/DB_HOST=db/g' "$SRC_DIR/.env"
    sed -i.bak 's/DB_DATABASE=.*/DB_DATABASE=todo/g' "$SRC_DIR/.env"
    sed -i.bak 's/DB_USERNAME=.*/DB_USERNAME=todo/g' "$SRC_DIR/.env"
    sed -i.bak 's/DB_PASSWORD=.*/DB_PASSWORD=secret/g' "$SRC_DIR/.env"
    
    # Remove backup file (macOS creates .bak files with sed -i)
    rm -f "$SRC_DIR/.env.bak" 2>/dev/null || true
  fi
}

# Perform initial setup
function initial_setup() {
  echo "Performing initial setup..."
  
  ensure_db_file
  ensure_scheduler_files
  ensure_env_file
  
  echo "Setup complete!"
}

# Setup a cron job on the host machine to run the scheduler
function setup_cron() {
  local cron_exists=$(crontab -l 2>/dev/null | grep -c "todo-list-scheduler" || true)
  
  if [ "$cron_exists" -eq 0 ]; then
    echo "Setting up cron job to run scheduler every hour..."
    
    # Create a temporary file with the existing crontab
    local tempfile=$(mktemp)
    crontab -l > "$tempfile" 2>/dev/null || echo "" > "$tempfile"
    
    # Add our new cron job
    echo "# todo-list-scheduler - Generate recurring todos" >> "$tempfile"
    echo "0 * * * * cd $(pwd) && ./manage.sh generate-instances 730 > /dev/null 2>&1" >> "$tempfile"
    
    # Install the new crontab
    crontab "$tempfile"
    rm "$tempfile"
    
    echo "Cron job installed successfully!"
  else
    echo "Cron job already exists. No changes made."
  fi
}

function remove_cron() {
  local cron_exists=$(crontab -l 2>/dev/null | grep -c "todo-list-scheduler" || true)
  
  if [ "$cron_exists" -gt 0 ]; then
    echo "Removing todo-list-scheduler cron job..."
    
    # Create a temporary file
    local tempfile=$(mktemp)
    
    # Filter out our cron job
    crontab -l | grep -v "todo-list-scheduler" > "$tempfile"
    
    # Install the new crontab
    crontab "$tempfile"
    rm "$tempfile"
    
    echo "Cron job removed successfully!"
  else
    echo "No cron job found. Nothing to remove."
  fi
}

case "$1" in
  setup)
    # Initial setup without starting containers
    initial_setup
    echo "Initial setup complete. Run './manage.sh start' to start the application."
    ;;
  start)
    check_docker
    initial_setup
    echo "Starting containers..."
    docker compose up -d
    
    echo "Waiting for services to initialize..."
    sleep 5
    
    echo "Running initial todo instance generation..."
    docker exec laravel_app php /var/www/html/artisan todos:generate-instances --days=730 || echo "Warning: Could not generate initial todo instances. Try running './manage.sh generate-instances' manually after a few moments."
    
    echo "All services started!"
    echo "Access the application at: http://localhost:8000"
    ;;
  stop)
    check_docker
    echo "Stopping containers..."
    docker compose down
    echo "Containers stopped."
    ;;
  restart)
    check_docker
    echo "Restarting containers..."
    docker compose down
    initial_setup
    docker compose up -d
    echo "Containers restarted."
    echo "Access the application at: http://localhost:8000"
    ;;
  generate-instances)
    check_docker
    DAYS=${2:-730}
    echo "Generating todo instances for $DAYS days..."
    docker exec laravel_app php /var/www/html/artisan todos:generate-instances --days=${DAYS}
    ;;
  run-scheduler)
    check_docker
    # Run the scheduler task directly on the Laravel app container
    echo "Running scheduler manually..."
    docker exec laravel_app php /var/www/html/artisan schedule:run --verbose
    ;;
  setup-cron)
    setup_cron
    ;;
  remove-cron)
    remove_cron
    ;;
  clear-cache)
    check_docker
    echo "Clearing Laravel cache..."
    docker exec laravel_app php /var/www/html/artisan cache:clear
    docker exec laravel_app php /var/www/html/artisan config:clear
    docker exec laravel_app php /var/www/html/artisan route:clear
    docker exec laravel_app php /var/www/html/artisan view:clear
    
    echo "Optimizing Laravel..."
    docker exec laravel_app php /var/www/html/artisan optimize:clear
    
    echo "Clearing asset cache..."
    # Create backup of manifest file if it exists
    docker exec laravel_app bash -c "mkdir -p /tmp/manifest_backup && cp -f /var/www/html/public/build/manifest.json /tmp/manifest_backup/ 2>/dev/null || true"
    
    # Clear cache but don't delete manifest
    docker exec laravel_app bash -c "cd /var/www/html && if command -v npm &> /dev/null; then npm cache clean --force; else find public/build -type f -not -name 'manifest.json' -delete 2>/dev/null || true; fi"
    
    # Restore manifest if backup exists, otherwise try to rebuild assets
    docker exec laravel_app bash -c "cd /var/www/html && if [ -f /tmp/manifest_backup/manifest.json ]; then mkdir -p public/build && cp -f /tmp/manifest_backup/manifest.json public/build/; else if command -v npm &> /dev/null; then echo 'Rebuilding assets...' && npm run build; fi; fi"
    
    echo "All caches cleared successfully!"
    
    # Now rebuild assets in the src directory
    echo "Rebuilding assets in src directory..."
    (cd src && npm run build) || echo "Failed to rebuild assets in src directory. You may need to run 'cd src && npm run build' manually."
    ;;
  build-assets)
    check_docker
    echo "Building assets in src directory..."
    (cd src && npm run build) || echo "Failed to build assets. Make sure Node.js and npm are installed in the src directory."
    
    echo "Building assets in container..."
    docker exec laravel_app bash -c "cd /var/www/html && if command -v npm &> /dev/null; then npm run build; else echo 'npm not available in container'; fi"
    ;;
  logs)
    check_docker
    if [ "$2" = "scheduler" ]; then
      echo "Scheduler logs from Laravel app container:"
      docker exec laravel_app cat /var/www/html/storage/logs/scheduler.log 2>/dev/null || echo "No scheduler logs found."
    else
      docker compose logs -f
    fi
    ;;
  *)
    echo "Todo List Scheduler Management Tool"
    echo ""
    echo "Usage: $0 {setup|start|stop|restart|generate-instances [days]|run-scheduler|setup-cron|remove-cron|clear-cache|build-assets|logs [scheduler]}"
    echo ""
    echo "Commands:"
    echo "  setup               Perform initial setup without starting containers"
    echo "  start               Set up and start all containers"
    echo "  stop                Stop all containers"
    echo "  restart             Restart all containers"
    echo "  generate-instances  Generate todo instances for the specified number of days (default: 730)"
    echo "  run-scheduler       Run the Laravel scheduler manually"
    echo "  setup-cron          Set up a cron job on the host machine to run the scheduler"
    echo "  remove-cron         Remove the scheduler cron job from the host machine"
    echo "  clear-cache         Clear Laravel and asset caches"
    echo "  build-assets        Build frontend assets"
    echo "  logs [scheduler]    View logs (specify 'scheduler' to view scheduler logs)"
    echo ""
    exit 1
    ;;
esac 