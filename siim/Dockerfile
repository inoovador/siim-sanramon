# syntax=docker/dockerfile:1.6
# SIIM — Sistema Inteligente de Imagen Municipal
# serversideup/php pre-built (nginx + php-fpm + extensions ya compiladas)

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
# Stage 3: runtime serversideup/php (nginx + php-fpm + extensions)
############################
FROM serversideup/php:8.3-fpm-nginx AS runtime

ENV SSL_MODE=off
ENV PHP_OPCACHE_ENABLE=1
ENV PHP_DATE_TIMEZONE=America/Lima
ENV S6_CMD_WAIT_FOR_SERVICES_MAXTIME=0
ENV AUTORUN_ENABLED=true
ENV AUTORUN_LARAVEL_STORAGE_LINK=true
ENV AUTORUN_LARAVEL_MIGRATION=true
ENV AUTORUN_LARAVEL_MIGRATION_ISOLATION=true
ENV AUTORUN_LARAVEL_DB_SEED=true
ENV AUTORUN_LARAVEL_DB_SEED_CLASS=Database\\Seeders\\DatabaseSeeder
ENV AUTORUN_LARAVEL_CONFIG_CACHE=true
ENV AUTORUN_LARAVEL_ROUTE_CACHE=true
ENV AUTORUN_LARAVEL_VIEW_CACHE=true

WORKDIR /var/www/html

COPY --chown=www-data:www-data --from=vendor /app /var/www/html
COPY --chown=www-data:www-data --from=assets /app/public/build /var/www/html/public/build

EXPOSE 8080

USER www-data
