# syntax=docker/dockerfile:1.6
# SIIM — Sistema Inteligente de Imagen Municipal
# FrankenPHP single-binary HTTP+PHP server

############################
# Stage 1: build assets
############################
FROM node:20-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json* ./
RUN npm ci --no-audit --no-fund
COPY resources ./resources
COPY vite.config.js tailwind.config.js postcss.config.js ./
RUN npm run build

############################
# Stage 2: composer vendor
############################
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --no-scripts --prefer-dist
COPY . .
RUN composer dump-autoload --optimize --classmap-authoritative --no-dev

############################
# Stage 3: runtime FrankenPHP
############################
FROM dunglas/frankenphp:1-php8.3 AS runtime

RUN install-php-extensions \
    pdo_mysql \
    intl \
    zip \
    bcmath \
    gd \
    opcache \
    pcntl \
    redis

WORKDIR /app

COPY --from=vendor /app /app
COPY --from=assets /app/public/build /app/public/build

RUN printf '{\n  auto_https off\n  admin off\n  persist_config off\n  frankenphp\n}\n\n:8080 {\n  root * /app/public\n  encode gzip\n  php_server\n}\n' > /etc/caddy/Caddyfile

RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache && \
    chmod -R 775 /app/storage /app/bootstrap/cache

EXPOSE 8080

RUN printf '#!/bin/sh\nset -e\ncd /app\nphp artisan config:cache || true\nphp artisan route:cache || true\nphp artisan view:cache || true\nphp artisan migrate --force || true\nphp artisan db:seed --force --class=Database\\\\Seeders\\\\DatabaseSeeder || true\nexec frankenphp run --config /etc/caddy/Caddyfile\n' > /usr/local/bin/start.sh && \
    chmod +x /usr/local/bin/start.sh

CMD ["/usr/local/bin/start.sh"]
