# syntax=docker/dockerfile:1.6
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --optimize-autoloader --no-scripts
COPY . .
RUN composer dump-autoload --optimize --classmap-authoritative --no-dev

FROM php:8.3-cli-alpine
RUN apk add --no-cache libzip icu libpng oniguruma mariadb-client \
 && apk add --no-cache --virtual .build-deps libzip-dev icu-dev libpng-dev oniguruma-dev mariadb-dev \
 && docker-php-ext-install -j$(nproc) pdo_mysql intl zip mbstring bcmath gd opcache pcntl \
 && apk del .build-deps
WORKDIR /app
COPY --from=vendor /app /app
RUN chown -R nobody:nobody storage bootstrap/cache && chmod -R 775 storage bootstrap/cache
EXPOSE 80
CMD ["sh","-c","php artisan config:cache; php artisan route:cache; php artisan view:cache; php artisan migrate --force; php artisan db:seed --force --class=Database\\\\Seeders\\\\DatabaseSeeder; exec php -S 0.0.0.0:80 -t public"]
