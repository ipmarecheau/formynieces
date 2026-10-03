FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    nginx \
    nodejs \
    npm \
    supervisor \
    sqlite \
    sqlite-dev \
    icu-dev \
    libzip-dev \
    git \
    curl \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_sqlite bcmath intl zip \
    && docker-php-ext-enable pdo_sqlite

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --optimize-autoloader \
    && npm install \
    && npm run build \
    && mkdir -p database \
    && chown -R www-data:www-data storage bootstrap/cache database \
    && chmod -R 775 storage bootstrap/cache database

COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf

# Bake the deployed commit SHA into the image so each environment can report exactly
# what code it is running (the /version endpoint). Passed by deploy.sh at build time.
ARG GIT_SHA=unknown
RUN echo "$GIT_SHA" > /var/www/html/version.txt

EXPOSE 8080

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
