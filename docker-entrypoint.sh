#!/bin/bash
set -e

# Mengatur permission storage dan cache jika volume di-mount
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Jika vendor belum ada (misal di-mount), jalankan composer install
if [ ! -d "vendor" ]; then
    echo "Running composer install..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# Jika .env belum ada, copy dari .env.example
if [ ! -f ".env" ]; then
    echo "Copying .env.example to .env..."
    cp .env.example .env
    php artisan key:generate
fi

echo "Clearing cache..."
php artisan optimize:clear

# Menjalankan migrasi database
echo "Running migrations..."
php artisan migrate --force

echo "Starting server..."
# Eksekusi command default (apache2-foreground)
exec "$@"
