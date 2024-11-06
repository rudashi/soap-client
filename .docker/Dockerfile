FROM composer:latest AS composer
FROM php:8.3-cli

RUN apt-get update && \
    apt-get install -y \
    libxml2-dev \
    libzip-dev \
    libonig-dev && \
    docker-php-ext-install soap mbstring xml zip && \
    pecl install xdebug && \
    docker-php-ext-enable xdebug

COPY --from=composer /usr/bin/composer /usr/local/bin/composer

RUN echo "xdebug.mode=coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

WORKDIR /var/www

COPY . .

CMD ["php", "-a"]