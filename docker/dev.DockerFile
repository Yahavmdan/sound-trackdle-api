# syntax=docker/dockerfile:1
FROM montebal/laradev:php80-2204 as dev


COPY ../vendor /var/www/html/vendor
COPY ../php.ini /etc/php8/conf.d/
