FROM php:8.1-fpm-alpine

# Instalar las extensiones de PHP necesarias para bases de datos MySQL/MariaDB
RUN docker-php-ext-install pdo_mysql mysqli
