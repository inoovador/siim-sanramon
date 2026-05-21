# syntax=docker/dockerfile:1.6
FROM php:8.3-cli-alpine

RUN apk add --no-cache git unzip libzip-dev icu-dev libpng-dev oniguruma-dev mariadb-dev \
 && docker-php-ext-install -j$(nproc) pdo_mysql intl zip mbstring bcmath gd opcache pcntl \
 && apk del icu-dev libpng-dev oniguruma-dev libzip-dev || true

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app
COPY . .
RUN composer install --no-dev --no-interaction --no-progress --optimize-autoloader

RUN apk add --no-cache nodejs npm \
 && npm ci --no-audit --no-fund && npm run build && rm -rf node_modules \
 && apk del nodejs npm || true

RUN chown -R nobody:nobody storage bootstrap/cache && chmod -R 775 storage bootstrap/cache

EXPOSE 80

CMD ["sh","-c","php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan migrate --force && php artisan db:seed --force --class=Database\\\\Seeders\\\\DatabaseSeeder && php -S 0.0.0.0:80 -t public"]
# rebuild 1779399163
