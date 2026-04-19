FROM php:8.2-apache

# Install system dependencies and PHP extensions for PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Enable Apache mod_rewrite for clean URLs
RUN a2enmod rewrite

# Copy your project (index.php is in root)
COPY . /var/www/html/

# (Optional) Set proper permissions
RUN chown -R www-data:www-data /var/www/html