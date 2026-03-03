FROM php:8.3-cli

# Install dependencies
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

# Copy ALL source first (quan trọng)
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Fix permission
RUN chmod -R 775 storage bootstrap/cache

# Start Laravel
CMD php artisan serve --host=0.0.0.0 --port=$PORT
