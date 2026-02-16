# docker/php/Dockerfile
FROM php:8.3-fpm-alpine

# System deps
RUN apk add --no-cache \
    bash \
    git \
    icu-dev \
    libzip-dev \
    libxml2-dev \
    oniguruma-dev \
    postgresql-dev \
    unzip \
    zip \
    $PHPIZE_DEPS

# PHP extensions required by Symfony + Postgres + Composer
RUN docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) \
    intl \
    opcache \
    pdo_pgsql \
    zip \
    xml \
    mbstring \
    && rm -rf /tmp/*

# Recommended PHP config (dev-friendly)
RUN { \
    echo "memory_limit=512M"; \
    echo "upload_max_filesize=32M"; \
    echo "post_max_size=32M"; \
    echo "max_execution_time=60"; \
    echo "date.timezone=Europe/Warsaw"; \
    } > /usr/local/etc/php/conf.d/99-agroflow.ini

# Opcache: sensible defaults for dev (enabled but conservative)
RUN { \
    echo "opcache.enable=1"; \
    echo "opcache.enable_cli=1"; \
    echo "opcache.validate_timestamps=1"; \
    echo "opcache.revalidate_freq=0"; \
    } > /usr/local/etc/php/conf.d/10-opcache-agroflow.ini

# Composer (copy from official image)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Keep container running as www-data (matches php-fpm user)
USER www-data
CMD ["php-fpm", "-F"]

