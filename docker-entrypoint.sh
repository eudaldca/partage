#!/bin/bash
set -e

# Wait for database to be ready
echo "Waiting for database connection..."
until php artisan db:show > /dev/null 2>&1; do
    echo "Database not ready, waiting..."
    sleep 2
done

echo "Database is ready!"

# Run migrations
echo "Running database migrations..."
php artisan migrate --force

# Clear and cache config for production
echo "Optimizing application..."
php artisan optimize

echo "Starting application..."

# Execute the main command passed to the container
exec "$@"
