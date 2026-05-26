FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    unzip \
    git \
    curl \
    sqlite3 \
    libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

RUN cp .env.example .env

RUN composer install --no-dev --optimize-autoloader

RUN touch database/database.sqlite

RUN php artisan key:generate

RUN php artisan storage:link

EXPOSE 10000

CMD php artisan serve --host=0.0.0.0 --port=10000 