FROM php:8.0-apache
WORKDIR /var/www/html

COPY index.php index.php
COPY req.tpl.html req.tpl.html

EXPOSE 80
