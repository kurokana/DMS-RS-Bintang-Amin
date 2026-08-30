#!/bin/bash
set -e

cd /var/www/html

mkdir -p database storage/framework/views storage/framework/sessions storage/framework/cache storage/logs bootstrap/cache
chmod -R 777 storage bootstrap/cache database

touch database/database.sqlite
chmod 666 database/database.sqlite

if [ ! -f .env ]; then
    cp .env.docker .env || cp .env.example .env
fi

if [ ! -f /var/www/html/vendor/autoload.php ]; then
    echo "Installing composer dependencies..."
    composer install --no-dev --prefer-dist --optimize-autoloader
fi

php artisan migrate --force

echo "Starting Laravel Reverb on 0.0.0.0:8080..."
php artisan reverb:start --host=0.0.0.0 --port=8080 &

echo "Starting Laravel API on 0.0.0.0:8000..."
exec php artisan serve --host=0.0.0.0 --port=8000
