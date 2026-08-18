#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

if [ ! -f ".env" ] && [ -f ".env.example" ]; then
  cp .env.example .env
fi

mkdir -p storage bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

if [ ! -d "vendor" ]; then
  composer install --no-interaction --prefer-dist
fi

if [ -f ".env" ] && ! grep -qE '^APP_KEY=base64:' .env 2>/dev/null; then
  php artisan key:generate --force || true
fi

if [ ! -e "public/storage" ]; then
  php artisan storage:link || true
fi

# Always clear stale caches so env changes take effect immediately
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true

# Run pending landlord migrations (only landlord-specific paths)
php artisan migrate --database=landlord --force --path=database/migrations/landlord 2>/dev/null || true

# Run pending tenant migrations for all existing tenants
php artisan tenants:migrate 2>/dev/null || true

# Re-cache for production performance
if [ "${APP_ENV:-local}" = "production" ]; then
  php artisan config:cache 2>/dev/null || true
  php artisan route:cache 2>/dev/null || true
  php artisan view:cache 2>/dev/null || true
fi

exec "$@"
