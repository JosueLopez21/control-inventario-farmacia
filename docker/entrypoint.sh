#!/bin/sh

set -eu

cd /var/www/html

if [ -z "${APP_KEY:-}" ]; then
    echo "ERROR: La variable de entorno APP_KEY no está definida." >&2
    echo "Genera una con: php artisan key:generate --show" >&2
    echo "y configúrala en Render antes de desplegar." >&2
    exit 1
fi

# Limpiamos cualquier caché que pudiera venir incluida en la imagen.
php artisan config:clear

# Migraciones idempotentes: nunca migrate:fresh ni db:seed en producción.
php artisan migrate --force

# Cachés de producción. Si alguna falla, el contenedor no arranca y Render muestra el error.
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Los comandos anteriores se ejecutan como root; devolvemos la propiedad a Apache.
chown -R www-data:www-data storage bootstrap/cache

exec apache2-foreground
