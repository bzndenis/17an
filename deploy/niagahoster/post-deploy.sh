#!/bin/bash
# Jalankan via SSH setelah upload (dari folder root project Laravel)
set -e

echo "==> 17an Dashboard — Post Deploy Niagahoster"

php -v

if [ ! -f .env ]; then
  echo "ERROR: File .env belum ada. Salin dari .env.production.example"
  exit 1
fi

composer install --no-dev --optimize-autoloader --no-interaction

php artisan key:generate --force --no-interaction 2>/dev/null || true

php artisan migrate --force --no-interaction
php artisan db:seed --force --no-interaction 2>/dev/null || echo "Seeder dilewati (opsional)"

php artisan storage:link --force 2>/dev/null || true

php artisan config:cache
php artisan route:cache
php artisan view:cache

chmod -R 775 storage bootstrap/cache 2>/dev/null || true

echo "==> Deploy selesai! Buka APP_URL di browser."
