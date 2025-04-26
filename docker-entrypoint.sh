# Ensure .env exists
if [ ! -f ".env" ]; then
  cp .env.example .env
fi

# Install PHP dependencies
composer install

# Generate app key if not already set
if ! grep -q "APP_KEY=base64" .env; then
  php artisan key:generate
fi

# Create session table migration
php artisan session:table

# Dump autoload to detect new migration
composer dump-autoload

# Run migrations
php artisan migrate --force

# Run tests
# php artisan test

# Optional: Seed the database (uncomment if needed)
# php artisan db:seed --force

# Start server
php artisan serve --host=0.0.0.0 --port=8000
