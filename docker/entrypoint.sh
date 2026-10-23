#!/usr/bin/env bash
set -euo pipefail

if [ -z "${APP_KEY:-}" ]; then
    echo "ERROR: APP_KEY no está definida." >&2
    echo "Genera una con 'php artisan key:generate --show' y configúrala en Render." >&2
    exit 1
fi

echo "Limpiando configuración previa..."
php artisan config:clear

echo "Ejecutando migraciones..."
php artisan migrate --force

echo "Generando cachés de producción..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Los comandos anteriores se ejecutan como root; devolvemos la propiedad a Apache.
chown -R www-data:www-data storage bootstrap/cache

echo "Iniciando Apache en el puerto ${PORT:-10000}..."
exec apache2-foreground
