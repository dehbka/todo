FROM php:8.5-fpm-alpine

# Install system dependencies (runtime) and build dependencies
RUN set -eux && \
    apk add --no-cache \
        bash \
        curl \
        git \
        unzip \
        libpq \
        icu-libs \
        oniguruma && \
    apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        build-base \
        icu-dev \
        postgresql-dev \
        oniguruma-dev

# Build PHP extensions serially to avoid Alpine parallel install header race (exit code 2)
# Note: opcache build on Alpine can intermittently fail (no module produced). We skip it for now
# to ensure reliable builds; it can be enabled later via package or different base image if needed.
RUN set -eux && \
    docker-php-ext-install -j1 \
        intl \
        pdo_pgsql \
        mbstring && \
    apk del --no-network .build-deps

# Install Composer (copy from official image)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Expose the php-fpm port (for clarity; compose links via service name)
EXPOSE 9000

CMD ["php-fpm"]
