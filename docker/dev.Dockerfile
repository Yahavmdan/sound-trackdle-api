# syntax=docker/dockerfile:1
FROM bitnami/laravel


#COPY ../vendor /var/www/html/vendor
COPY ../docker/custom-php.ini /etc/php/8.1/cli/php.ini
COPY ../docker/custom-php.ini /etc/php/8.1/apache2/php.ini
