#!/usr/bin/env bash
set -euo pipefail

# Single-server deployment script for SPIN MEIS 2.0
# Target: Ubuntu/Debian with Nginx, PHP 8.3-FPM, PostgreSQL, Supervisor (queue + scheduler)

APP_DIR="${APP_DIR:-/var/www/spin-meis}"
PHP_BIN="${PHP_BIN:-php8.3}"

echo "==> Deploying SPIN MEIS 2.0 in ${APP_DIR}"
cd "${APP_DIR}"

echo "==> Pulling latest code"
git pull --ff-only

echo "==> Installing PHP dependencies"
composer install --no-dev --optimize-autoloader --no-interaction

echo "==> Building frontend assets"
npm ci --omit=dev
npm run build

echo "==> Enabling maintenance mode"
${PHP_BIN} artisan down --retry=60 || true

echo "==> Running database migrations"
${PHP_BIN} artisan migrate --force

echo "==> Clearing and warming caches"
${PHP_BIN} artisan config:cache
${PHP_BIN} artisan route:cache
${PHP_BIN} artisan view:cache
${PHP_BIN} artisan event:cache

echo "==> Restarting queue workers"
${PHP_BIN} artisan queue:restart

echo "==> Disabling maintenance mode"
${PHP_BIN} artisan up

echo "==> Deployment complete"
