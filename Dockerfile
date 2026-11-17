FROM jkaninda/nginx-php-fpm:8.4

ARG BUILD_DATE
ARG VCS_REF
ARG VERSION="dev"

LABEL maintainer="Jude Mpoyo <mpoyojude0@gmail.com>" \
    org.opencontainers.image.authors="Jude Mpoyo <mpoyojude0@gmail.com>" \
    org.opencontainers.image.title="lara-api-starter" \
    org.opencontainers.image.description="LaraApiStarter - A Modular Laravel API Starter Kit" \
    org.opencontainers.image.source="https://github.com/judempoyo/laraApiStarter" \
    org.opencontainers.image.licenses="MIT" \
    org.opencontainers.image.version="${VERSION}" \
    org.opencontainers.image.revision="${VCS_REF}" \
    org.opencontainers.image.created="${BUILD_DATE}"

WORKDIR /var/www/html

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_CACHE_DIR=/root/.cache/composer

# ---------------------------------------------------------
# Composer dependencies
# ---------------------------------------------------------
COPY composer.json composer.lock ./

RUN --mount=type=cache,target=/root/.cache/composer \
    composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

# ---------------------------------------------------------
# Application
# ---------------------------------------------------------
COPY . .

RUN composer dump-autoload --optimize --no-dev

# ---------------------------------------------------------
# Laravel writable directories & storage link
# ---------------------------------------------------------
RUN mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/app/public \
    storage/logs \
    bootstrap/cache \
    && php artisan storage:link \
    && chown -R www-data:www-data \
    storage \
    bootstrap/cache

EXPOSE 80