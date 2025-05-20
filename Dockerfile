# Use official PHP image with Apache
FROM php:8.2-apache

# Install system dependencies and PHP extensions
RUN apt-get update && \
    apt-get install -y \
        libzip-dev \
        zip \
        && \
    docker-php-ext-install pdo pdo_mysql zip

# Enable Apache rewrite module
RUN a2enmod rewrite

# Copy all files to the container
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html

# Install Composer dependencies (if you use composer)
RUN if [ -f "composer.json" ]; then \
        curl -sS https://getcomposer.org/installer | php -- \
        && mv composer.phar /usr/local/bin/composer \
        && composer install --no-dev; \
    fi

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html
