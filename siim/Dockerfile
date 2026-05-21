# syntax=docker/dockerfile:1.6

############################
# Stage 1: build assets
############################
FROM node:20-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json* ./
RUN npm ci
COPY resources ./resources
COPY vite.config.js tailwind.config.js postcss.config.js ./
RUN npm run build

############################
# Stage 2: composer install
############################
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --no-scripts --prefer-dist
COPY . .
RUN composer dump-autoload --optimize --classmap-authoritative --no-dev

############################
# Stage 3: runtime
############################
FROM php:8.3-fpm-alpine AS runtime

RUN apk add --no-cache \
    icu-dev libpng-dev libzip-dev oniguruma-dev mariadb-dev nginx supervisor \
 && docker-php-ext-install -j$(nproc) pdo_mysql intl zip mbstring bcmath gd opcache pcntl

# Redis
RUN apk add --no-cache --virtual .build-deps autoconf g++ make \
 && pecl install redis \
 && docker-php-ext-enable redis \
 && apk del .build-deps

WORKDIR /var/www/html
COPY --from=vendor /app /var/www/html
COPY --from=assets /app/public/build ./public/build

RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
