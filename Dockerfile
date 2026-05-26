FROM php:8.3-cli

WORKDIR /app

COPY . .

RUN apt-get update && apt-get install -y \
    unzip \
    git \
    libsqlite3-dev \
    sqlite3 \
    && docker-php-ext-install pdo pdo_sqlite

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN composer install

# crear sqlite
RUN mkdir -p database
RUN touch database/database.sqlite

# permisos
RUN chmod -R 777 storage bootstrap/cache database

# generar cache
RUN php artisan config:clear
RUN php artisan cache:clear

# migraciones
RUN php artisan migrate --force

EXPOSE 10000

CMD php artisan serve --host=0.0.0.0 --port=10000