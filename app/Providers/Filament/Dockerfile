FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    curl zip unzip git nodejs npm libpq-dev

RUN docker-php-ext-install pdo pdo_pgsql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

RUN composer install --no-dev
RUN npm install && npm run build

EXPOSE 8000
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=8000