FROM php:8.3-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy only composer files first (better caching)
COPY composer.json composer.lock ./

RUN composer install --no-dev --optimize-autoloader

# Copy rest of source
COPY . .

# Set proper permissions
RUN chmod -R 775 storage bootstrap/cache

# Expose Railway dynamic port
EXPOSE 8080

# Start Laravel server
CMD php artisan serve --host=0.0.0.0 --port=$PORT
