FROM jkaninda/nginx-php-fpm:8.4

WORKDIR /var/www/html

# ---------------------------------------------------------
# Composer dependencies
# ---------------------------------------------------------
COPY composer.json composer.lock ./

RUN composer install \
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

RUN test -s resources/views/mail/notification.blade.php \
    && test -s resources/views/vendor/notifications/email.blade.php \
    && test -s resources/views/vendor/mail/html/notification.blade.php \
    && test -s resources/views/vendor/mail/text/notification.blade.php \
    && test -s resources/views/vendor/mail/html/themes/default.css

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