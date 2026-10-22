# ==============================================================================
# Stage 1: base — PHP-FPM runtime, required Botble/Laravel extensions & Composer
# ==============================================================================
FROM php:8.4.27-fpm-alpine3.24 AS base

WORKDIR /var/www/html

RUN apk add --no-cache \
        curl \
        fcgi \
        freetype \
        git \
        icu-libs \
        libjpeg-turbo \
        libpng \
        libwebp \
        libzip \
        unzip \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        freetype-dev \
        icu-dev \
        libjpeg-turbo-dev \
        libpng-dev \
        libwebp-dev \
        libzip-dev \
        linux-headers \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        exif \
        gd \
        intl \
        opcache \
        pcntl \
        pdo_mysql \
        zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps \
    && rm -rf /tmp/pear /var/cache/apk/*

COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]

# ==============================================================================
# Stage 2: dev — local development with Xdebug (off by default, toggled via env)
# ==============================================================================
FROM base AS dev

ENV XDEBUG_MODE=off \
    XDEBUG_CLIENT_HOST=host.docker.internal \
    XDEBUG_CLIENT_PORT=9003

RUN apk add --no-cache --virtual .xdebug-build-deps \
        $PHPIZE_DEPS \
        linux-headers \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apk del .xdebug-build-deps \
    && rm -rf /tmp/pear /var/cache/apk/*

COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-custom.ini

COPY --chown=www-data:www-data . /var/www/html

RUN mkdir -p \
        storage/app/public \
        storage/app/purifier \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R a+rwX storage bootstrap/cache

# ==============================================================================
# Stage 3: prod — production runtime with optimized autoloader & OPcache enabled
# ==============================================================================
FROM base AS prod

ENV APP_ENV=production \
    APP_DEBUG=false

COPY docker/php/php.prod.ini /usr/local/etc/php/conf.d/99-custom.ini

COPY --chown=www-data:www-data . /var/www/html

RUN mkdir -p \
        storage/app/public \
        storage/app/purifier \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && composer install \
        --no-dev \
        --optimize-autoloader \
        --no-interaction \
        --no-progress \
        --prefer-dist \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R a+rwX storage bootstrap/cache
