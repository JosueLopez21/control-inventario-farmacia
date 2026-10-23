# syntax=docker/dockerfile:1

# ---------------------------------------------------------------------------
# Etapa 1: dependencias de PHP con Composer
# ---------------------------------------------------------------------------
FROM composer:2 AS vendor

WORKDIR /app

# Copiamos primero los archivos de Composer para aprovechar la caché de capas.
COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-scripts \
    --prefer-dist \
    --no-interaction

# ---------------------------------------------------------------------------
# Etapa 2: imagen final con Apache y PHP
# ---------------------------------------------------------------------------
FROM php:8.4-apache

# Render define PORT al iniciar el contenedor. El valor por defecto es 10000.
ENV PORT=10000

# Extensiones necesarias para PostgreSQL, cadenas, matemáticas y compresión.
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libpq-dev \
        libzip-dev \
        libonig-dev \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_pgsql \
        pgsql \
        mbstring \
        bcmath \
        zip \
        opcache \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Apache escucha en el puerto que Render inyecta mediante la variable PORT.
# Las comillas simples evitan que el shell de Docker expanda ${PORT} al construir.
RUN sed -i 's/^Listen 80/Listen ${PORT}/' /etc/apache2/ports.conf

# DocumentRoot apunta a public/ y se permite .htaccess (front controller de Laravel).
RUN printf '%s\n' \
    '<VirtualHost *:${PORT}>' \
    '    ServerName localhost' \
    '    DocumentRoot /var/www/html/public' \
    '' \
    '    <Directory /var/www/html/public>' \
    '        Options FollowSymLinks' \
    '        AllowOverride All' \
    '        Require all granted' \
    '    </Directory>' \
    '' \
    '    ErrorLog ${APACHE_LOG_DIR}/error.log' \
    '    CustomLog ${APACHE_LOG_DIR}/access.log combined' \
    '</VirtualHost>' \
    > /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

# Dependencias instaladas en la etapa anterior.
COPY --from=vendor /app/vendor ./vendor

# Código de la aplicación (vendor, .env y otros archivos se excluyen en .dockerignore).
COPY . .

# Directorios de escritura y script de arranque.
RUN mkdir -p \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache \
    && chmod +x docker/entrypoint.sh

EXPOSE 10000

ENTRYPOINT ["/var/www/html/docker/entrypoint.sh"]
