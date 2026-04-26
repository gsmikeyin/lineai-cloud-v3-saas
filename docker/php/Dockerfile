FROM php:8.2-fpm

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        curl \
        zip \
        unzip \
        libzip-dev \
        libicu-dev \
        libonig-dev \
        libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql intl zip pcntl opcache \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY docker/php/php.ini /usr/local/etc/php/conf.d/lineai.ini
COPY docker/php/entrypoint.sh /usr/local/bin/lineai-entrypoint

RUN chmod +x /usr/local/bin/lineai-entrypoint

ENTRYPOINT ["lineai-entrypoint"]
CMD ["php-fpm"]
