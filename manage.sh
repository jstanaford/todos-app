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
function perform_setup() {
  echo "Performing initial setup..."
  
  ensure_db_file
  ensure_scheduler_files
  ensure_env_file
  
  echo "Setup complete!"
}

# Wait for container to be ready
function wait_for_container() {
  local container=$1
  local max_attempts=$2
  local delay=$3
  local check_command=$4
  
  echo "Waiting for $container to be ready..."
  
  for ((i=1; i<=max_attempts; i++)); do
    echo "Attempt $i of $max_attempts..."
    
    if docker exec $container $check_command &>/dev/null; then
      echo "$container is ready!"
      return 0
    fi
    
    echo "Still waiting for $container... (sleeping for $delay seconds)"
    sleep $delay
  done
  
  echo "Timed out waiting for $container to be ready"
  return 1
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
  start)
    check_docker
    
    # Always run setup during start
    perform_setup
    
    echo "Starting containers..."
    docker compose up -d
    
    echo "Waiting for containers to initialize..."
    
    # Wait for the Laravel app container to be ready with Composer dependencies installed
    # Try for up to 2 minutes (12 attempts with 10 second delay)
    if wait_for_container laravel_app 12 10 "test -d /var/www/html/vendor"; then
      echo "Dependencies installed successfully."
      
      # Now wait for the Laravel artisan command to be available
      if wait_for_container laravel_app 3 5 "php /var/www/html/artisan --version"; then
        echo "Laravel is ready. Running initial todo instance generation..."
        
        # Check if the Vite manifest exists, if not, build assets
        if ! docker exec laravel_app test -f /var/www/html/public/build/manifest.json &>/dev/null; then
          echo "Vite manifest not found. Building assets..."
          docker exec laravel_app bash -c "cd /var/www/html && npm run build"
        fi
        
        # Check if migrations need to be run (look for migration files)
        if docker exec laravel_app test -d /var/www/html/database/migrations &>/dev/null; then
          echo "Running database migrations and seeders..."
          docker exec laravel_app php /var/www/html/artisan migrate --force
          docker exec laravel_app php /var/www/html/artisan db:seed --force --no-interaction
        fi
        
        docker exec laravel_app php /var/www/html/artisan todos:generate-instances --days=730
      else
        echo "Warning: Laravel does not appear to be ready. Try running './manage.sh generate-instances' manually after a few moments."
      fi
    else
      echo "Warning: Dependencies installation is taking longer than expected."
      echo "You may need to wait a bit longer and then run './manage.sh generate-instances' manually."
    fi
    
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
    
    # Always run setup during restart
    perform_setup
    
    docker compose up -d
    
    echo "Waiting for containers to initialize..."
    wait_for_container laravel_app 12 10 "test -d /var/www/html/vendor" || true
    
    echo "Containers restarted."
    echo "Access the application at: http://localhost:8000"
    ;;
  restart-app)
    check_docker
    echo "Restarting Laravel app container..."
    
    # Stop and remove only the Laravel app container
    docker stop laravel_app
    docker rm laravel_app
    
    # Start the container again
    docker compose up -d app
    
    echo "Waiting for Laravel app to initialize..."
    wait_for_container laravel_app 12 10 "test -d /var/www/html/vendor" || true
    
    # Check if the Vite manifest exists, if not, build assets
    if ! docker exec laravel_app test -f /var/www/html/public/build/manifest.json &>/dev/null; then
      echo "Vite manifest not found. Building assets..."
      docker exec laravel_app bash -c "cd /var/www/html && npm install && npm run build"
    fi
    
    echo "Laravel app container restarted."
    echo "Access the application at: http://localhost:8000"
    ;;
  generate-instances)
    check_docker
    
    # Check if we need to wait for dependencies
    if ! docker exec laravel_app test -d /var/www/html/vendor &>/dev/null; then
      echo "Waiting for Composer dependencies to be installed..."
      wait_for_container laravel_app 12 10 "test -d /var/www/html/vendor" || {
        echo "Error: Dependencies not installed. Please check the container logs with './manage.sh logs'"
        exit 1
      }
    fi
    
    DAYS=${2:-730}
    echo "Generating todo instances for $DAYS days..."
    docker exec laravel_app php /var/www/html/artisan todos:generate-instances --days=${DAYS}
    ;;
  run-scheduler)
    check_docker
    
    # Check if we need to wait for dependencies
    if ! docker exec laravel_app test -d /var/www/html/vendor &>/dev/null; then
      echo "Waiting for Composer dependencies to be installed..."
      wait_for_container laravel_app 12 10 "test -d /var/www/html/vendor" || {
        echo "Error: Dependencies not installed. Please check the container logs with './manage.sh logs'"
        exit 1
      }
    fi
    
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
    
    # Check if we need to wait for dependencies
    if ! docker exec laravel_app test -d /var/www/html/vendor &>/dev/null; then
      echo "Waiting for Composer dependencies to be installed..."
      wait_for_container laravel_app 12 10 "test -d /var/www/html/vendor" || {
        echo "Error: Dependencies not installed. Please check the container logs with './manage.sh logs'"
        exit 1
      }
    fi
    
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
    
    # Check if we need to wait for dependencies
    if ! docker exec laravel_app test -d /var/www/html/vendor &>/dev/null; then
      echo "Waiting for Composer dependencies to be installed..."
      wait_for_container laravel_app 12 10 "test -d /var/www/html/vendor" || {
        echo "Warning: Dependencies may not be fully installed. Proceeding anyway..."
      }
    fi
    
    echo "Building assets in src directory..."
    (cd src && npm run build) || echo "Failed to build assets in src directory. You may need to run 'cd src && npm run build' manually."
    
    echo "Building assets in container..."
    docker exec laravel_app bash -c "cd /var/www/html && npm install && npm run build"
    
    # Verify the manifest was created
    if docker exec laravel_app test -f /var/www/html/public/build/manifest.json; then
      echo "✅ Vite manifest successfully created."
    else
      echo "❌ Warning: Vite manifest not created. There may be an issue with the build process."
      echo "Try running the following commands directly:"
      echo "docker exec -it laravel_app bash -c 'cd /var/www/html && npm run build'"
    fi
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
  db-seed)
    check_docker
    
    # Check if we need to wait for dependencies
    if ! docker exec laravel_app test -d /var/www/html/vendor &>/dev/null; then
      echo "Waiting for Composer dependencies to be installed..."
      wait_for_container laravel_app 12 10 "test -d /var/www/html/vendor" || {
        echo "Error: Dependencies not installed. Please check the container logs with './manage.sh logs'"
        exit 1
      }
    fi
    
    echo "Running database seeders..."
    docker exec laravel_app php /var/www/html/artisan db:seed --force --no-interaction
    ;;
    
  db-refresh)
    check_docker
    
    # Check if we need to wait for dependencies
    if ! docker exec laravel_app test -d /var/www/html/vendor &>/dev/null; then
      echo "Waiting for Composer dependencies to be installed..."
      wait_for_container laravel_app 12 10 "test -d /var/www/html/vendor" || {
        echo "Error: Dependencies not installed. Please check the container logs with './manage.sh logs'"
        exit 1
      }
    fi
    
    echo "Refreshing the database (all data will be lost)..."
    docker exec laravel_app php /var/www/html/artisan migrate:fresh --force
    
    echo "Running database seeders..."
    docker exec laravel_app php /var/www/html/artisan db:seed --force --no-interaction
    
    echo "Generating todo instances..."
    docker exec laravel_app php /var/www/html/artisan todos:generate-instances --days=730
    ;;
  *)
    echo "Todo List Scheduler Management Tool"
    echo ""
    echo "Usage: $0 {start|stop|restart|restart-app|generate-instances [days]|run-scheduler|setup-cron|remove-cron|clear-cache|build-assets|logs [scheduler]|db-seed|db-refresh}"
    echo ""
    echo "Commands:"
    echo "  start               Set up and start all containers"
    echo "  stop                Stop all containers"
    echo "  restart             Restart all containers"
    echo "  restart-app         Restart the Laravel app container"
    echo "  generate-instances  Generate todo instances for the specified number of days (default: 730)"
    echo "  run-scheduler       Run the Laravel scheduler manually"
    echo "  setup-cron          Set up a cron job on the host machine to run the scheduler"
    echo "  remove-cron         Remove the scheduler cron job from the host machine"
    echo "  clear-cache         Clear Laravel and asset caches"
    echo "  build-assets        Build frontend assets"
    echo "  logs [scheduler]    View logs (specify 'scheduler' to view scheduler logs)"
    echo "  db-seed             Run database seeders"
    echo "  db-refresh          Refresh the database (all data will be lost)"
    echo ""
    exit 1
    ;;
esac 