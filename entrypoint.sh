# Run migrations
echo "Running database migrations..."
php artisan migrate --force

# Clear and cache config for production
echo "Optimizing application..."
php artisan optimize

# Ensure storage/framework subdirectories exist
echo "Ensuring storage directories exist..."
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views

echo "Starting application..."

# Execute the main command passed to the container
exec "$@"
