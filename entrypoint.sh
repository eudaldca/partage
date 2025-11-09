#!/bin/sh
set -e

echo "Running database migrations..."
php artisan migrate --force

echo "Optimizing application..."
php artisan optimize

echo "Ensuring storage directories exist..."
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
chown -R www-data:www-data storage/framework

echo "Starting application..."

exec "$@"
