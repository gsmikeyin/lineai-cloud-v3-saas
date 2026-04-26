#!/usr/bin/env sh
set -e

cd /var/www

if [ ! -f .env ] && [ -f .env.example ]; then
    cp .env.example .env
fi

if [ ! -d vendor ]; then
    composer install --no-interaction --prefer-dist
fi

if [ -f artisan ]; then
    mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache

    if ! grep -q '^APP_KEY=base64:' .env 2>/dev/null; then
        php artisan key:generate --force --no-interaction
    fi

    php artisan storage:link --no-interaction >/dev/null 2>&1 || true
fi

exec "$@"
