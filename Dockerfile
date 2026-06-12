FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    nginx \
    nodejs \
    npm \
    supervisor \
    sqlite \
    sqlite-dev \
    git \
    curl \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_sqlite bcmath \
    && docker-php-ext-enable pdo_sqlite

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --optimize-autoloader \
    && npm install \
    && npm run build \
    && mkdir -p database \
    && touch database/database.sqlite \
    && chown -R www-data:www-data storage bootstrap/cache database \
    && chmod -R 775 storage bootstrap/cache database

COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf

EXPOSE 8080

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]