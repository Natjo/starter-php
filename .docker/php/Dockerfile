FROM php:8-fpm

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        $PHPIZE_DEPS \
        chromium \
        imagemagick \
        libmagickwand-dev \
        nodejs \
        npm; \
    npm install -g lighthouse; \
    pecl install imagick; \
    docker-php-ext-enable imagick; \
    apt-get purge -y --auto-remove $PHPIZE_DEPS; \
    rm -rf /var/lib/apt/lists/*
