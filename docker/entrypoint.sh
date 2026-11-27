#!/usr/bin/env bash
set -euo pipefail

export PORT="${PORT:-8080}"

echo "==> Rendering nginx config for PORT=${PORT}"
envsubst '${PORT}' < /etc/nginx/templates/default.conf.template > /etc/nginx/conf.d/default.conf

echo "==> Preparing Laravel storage directories"
mkdir -p \
  storage/app/private \
  storage/app/public \
  storage/framework/cache/data \
  storage/framework/sessions \
  storage/framework/testing \
  storage/framework/views \
  storage/logs \
  bootstrap/cache

# Ensure writable permissions for Laravel runtime dirs.
chown -R www-data:www-data storage bootstrap/cache || true
chmod -R ug+rwX storage bootstrap/cache || true

# Public storage symlink (safe if unused).
php artisan storage:link --force --no-interaction >/dev/null 2>&1 || true

if [ -z "${APP_KEY:-}" ]; then
  echo "WARNING: APP_KEY is not set. Set APP_KEY in Render Environment before using the app."
fi

# Cache for production only when APP_KEY is present (config cache requires a valid app key).
if [ "${APP_ENV:-production}" = "production" ] && [ -n "${APP_KEY:-}" ]; then
  echo "==> Caching Laravel configuration"
  php artisan config:clear --no-interaction || true
  php artisan config:cache --no-interaction
  php artisan route:cache --no-interaction
  php artisan view:cache --no-interaction
  php artisan event:cache --no-interaction || true
else
  echo "==> Skipping production cache (APP_ENV=${APP_ENV:-unset}, APP_KEY set=$([ -n "${APP_KEY:-}" ] && echo yes || echo no))"
fi

# Optional: run migrations explicitly. Disabled by default for safety.
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
  echo "==> Running database migrations (--force)"
  php artisan migrate --force --no-interaction
else
  echo "==> Skipping migrations (set RUN_MIGRATIONS=true to enable explicitly)"
fi

echo "==> Starting services"
exec "$@"
