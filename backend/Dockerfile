FROM php:8.3-cli

# Allow composer to run as superuser in Docker container
ENV COMPOSER_ALLOW_SUPERUSER=1

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

# Copy repository files
COPY . .

# Ensure storage & cache directories exist with full permissions
RUN if [ -d "backend" ]; then \
        mkdir -p backend/storage/framework/sessions \
                 backend/storage/framework/views \
                 backend/storage/framework/cache/data \
                 backend/storage/app/public/proof_images \
                 backend/storage/logs \
                 backend/bootstrap/cache && \
        chmod -R 777 backend/storage backend/bootstrap/cache; \
    else \
        mkdir -p storage/framework/sessions \
                 storage/framework/views \
                 storage/framework/cache/data \
                 storage/app/public/proof_images \
                 storage/logs \
                 bootstrap/cache && \
        chmod -R 777 storage bootstrap/cache; \
    fi

# Run composer install where composer.json exists (either /app or /app/backend)
RUN if [ -f "composer.json" ]; then \
        composer install --no-dev --optimize-autoloader; \
    elif [ -f "backend/composer.json" ]; then \
        cd backend && composer install --no-dev --optimize-autoloader; \
    else \
        echo "composer.json not found"; exit 1; \
    fi

EXPOSE 8000

CMD ["sh", "-c", "if [ -d 'backend' ]; then cd backend; fi && mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache/data storage/logs bootstrap/cache && chmod -R 777 storage bootstrap/cache && php artisan config:clear && php artisan cache:clear && php artisan storage:link && php artisan serve --host=0.0.0.0 --port=${PORT:-8000}"]
