#!/usr/bin/env sh
set -e

if [ ! -f /app/vendor/autoload.php ]; then
  echo "Composer autoload missing. Installing dependencies..."
  composer install --no-dev --prefer-dist --no-interaction
fi

exec php -S 0.0.0.0:8000 -t /app
