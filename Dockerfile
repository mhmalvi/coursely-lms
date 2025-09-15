# Use PHP 7.4 with Apache (compatible with Laravel requirements)
FROM php:7.4-apache

# Build argument for GitHub token (optional)
ARG GITHUB_TOKEN=""

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libgmp-dev \
    zip \
    unzip \
    nodejs \
    npm \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip gmp \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy existing application directory contents
COPY . /var/www/html

# Create necessary directories and set permissions
RUN mkdir -p /var/www/html/storage/logs \
    && mkdir -p /var/www/html/storage/framework/sessions \
    && mkdir -p /var/www/html/storage/framework/views \
    && mkdir -p /var/www/html/storage/framework/cache \
    && mkdir -p /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Configure Git for Composer and install PHP dependencies
RUN git config --global url."https://".insteadOf git:// && \
    git config --global http.sslVerify false && \
    composer config --global github-protocols https ssh && \
    if [ -n "$GITHUB_TOKEN" ]; then \
        composer config --global github-oauth.github.com $GITHUB_TOKEN; \
    fi && \
    (composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs --prefer-dist || \
     composer update --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs --prefer-dist)

# Install Node dependencies and build assets
RUN npm ci --production=false && npm run production && npm cache clean --force

# Set final permissions
RUN chown -R www-data:www-data /var/www/html

# Configure Apache
COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf

# Expose port 80
EXPOSE 80

# Create entrypoint script
RUN echo '#!/bin/bash\n\
php artisan migrate --force\n\
php artisan config:cache\n\
php artisan route:cache\n\
php artisan view:cache\n\
apache2-foreground' > /entrypoint.sh && chmod +x /entrypoint.sh

# Start with entrypoint
CMD ["/entrypoint.sh"]