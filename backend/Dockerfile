FROM php:8.3-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libcurl4-openssl-dev \
    zip \
    unzip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions required by Laravel
RUN docker-php-ext-install pdo_mysql mbstring bcmath gd xml zip curl

# Copy Composer binary from official image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy backend files
COPY . .

# Install PHP composer dependencies
RUN composer install --no-dev --optimize-autoloader

EXPOSE 8000

CMD ["sh", "-c", "php artisan storage:link && php artisan serve --host=0.0.0.0 --port=${PORT:-8000}"]
