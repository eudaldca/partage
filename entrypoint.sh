#!/bin/sh
set -e

# Set default PUID and PGID if not provided
UID=${UID:-1000}
GID=${GID:-1000}

echo "Setting up user with PUID=$UID and PGID=$GID..."

# Modify www-data user and group to match host IDs
groupmod -o -g "$GID" www-data
usermod -o -u "$UID" www-data

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
