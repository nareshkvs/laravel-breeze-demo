FROM php:8.4-fpm

ARG NODE_VERSION=18.x

RUN apt-get update && apt-get install -y \
    unzip \
    git \
    curl \
    zip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    default-mysql-client \
    ca-certificates \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        zip \
        mbstring \
        bcmath \
        intl \
        exif \
        pcntl

# Install Node.js (provides npm) from NodeSource
RUN curl -fsSL https://deb.nodesource.com/setup_${NODE_VERSION} | bash - \
    && apt-get install -y nodejs

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Xdebug but keep it disabled by default
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && echo "xdebug.mode=off" > /usr/local/etc/php/conf.d/xdebug.ini

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy rest of application
COPY . /var/www

# Copy composer files first for better caching
COPY composer.json composer.lock ./

# Install PHP dependencies (including dev deps so Breeze can be required/installed)
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Make docker-entrypoint executable
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh \
    && sed -i 's/\r$//' /usr/local/bin/docker-entrypoint.sh || true

# Ensure permissions for Laravel
RUN chown -R www-data:www-data /var/www/storage \
    && chown -R www-data:www-data /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage \
    && chmod -R 775 /var/www/bootstrap/cache

# Install JS dependencies and build assets
RUN if [ -f package-lock.json ] || [ -f yarn.lock ]; then \
      npm ci --unsafe-perm || npm install --unsafe-perm; \
    else \
      npm install --unsafe-perm; \
    fi

# Build production assets
RUN npm run build || npm run dev || true

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm", "-F"]