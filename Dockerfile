FROM php:8.2-fpm

# Install necessary extension for Laravel
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo_mysql zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy source code into container
COPY . .

# Install dependency
RUN composer install --no-dev --optimize-autoloader

CMD ["php-fpm"]
