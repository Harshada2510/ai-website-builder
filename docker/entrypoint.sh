#!/bin/sh
set -eu

export PORT="${PORT:-8080}"

# TiDB/MySQL TLS CA certificate (public CA only; never log certificate contents)
TIDB_CA_TARGET="/etc/ssl/certs/tidb-ca.pem"
TIDB_CA_SOURCE="${TIDB_CA_SECRET_FILE:-/etc/secrets/tidb-ca.pem}"
if [ ! -f "$TIDB_CA_TARGET" ] && [ -f "$TIDB_CA_SOURCE" ]; then
  echo "==> Installing TiDB CA certificate from secret file"
  cp "$TIDB_CA_SOURCE" "$TIDB_CA_TARGET"
  chmod 0644 "$TIDB_CA_TARGET" || true
fi
if [ -f "$TIDB_CA_TARGET" ] && [ -z "${MYSQL_ATTR_SSL_CA:-}" ]; then
  export MYSQL_ATTR_SSL_CA="$TIDB_CA_TARGET"
  echo "==> MYSQL_ATTR_SSL_CA configured from mounted CA certificate"
fi
if [ -n "${MYSQL_ATTR_SSL_CA:-}" ] && [ ! -f "${MYSQL_ATTR_SSL_CA}" ]; then
  echo "WARNING: MYSQL_ATTR_SSL_CA is set but the certificate file was not found. Database TLS connection may fail."
fi

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
