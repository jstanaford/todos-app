#!/bin/bash
set -e

echo "Starting scheduler container..."

# Go to the application directory
cd /var/www/html

# Print PHP version for debugging
echo "PHP Version:"
/usr/local/bin/php -v || echo "ERROR: PHP not found"

# Print environment for debugging
echo "Environment PATH: $PATH"
echo "Working directory: $(pwd)"

# Install PHP dependencies if they don't exist
if [ ! -d "vendor" ]; then
    echo "Installing PHP dependencies..."
    /usr/bin/composer install --no-interaction --no-dev --optimize-autoloader
fi

# Ensure log files exist and are writable
mkdir -p storage/logs
touch storage/logs/laravel.log
touch storage/logs/scheduler.log
touch /var/log/cron.log
chmod -R 777 storage/logs
chmod 777 /var/log/cron.log

# Ensure storage is writable
echo "Setting permissions for storage and cache directories..."
chmod -R 777 storage
chmod -R 777 bootstrap/cache

# Run initial instance generation to ensure we have a baseline
echo "Running initial todo instance generation..."
/usr/local/bin/php artisan todos:generate-instances --days=730

echo "=============== SWITCHING TO DIRECT SCHEDULER MODE ==============="
echo "Skipping cron setup - running scheduler directly..."

# Instead of using cron, we'll run the scheduler in a loop
while true; do
    echo "$(date): Running scheduler..."
    /usr/local/bin/php artisan schedule:run --verbose >> /var/log/cron.log 2>&1
    
    # Sleep for 60 seconds
    sleep 60
done 