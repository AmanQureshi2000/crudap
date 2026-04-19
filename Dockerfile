FROM php:8.2-apache

# Install system dependencies and PHP extensions for PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Enable Apache mod_rewrite for clean URLs
RUN a2enmod rewrite

# Copy your project (NO .env copy needed)
COPY . /var/www/html/

# Create empty .env file to prevent the "file not found" error
# Your Render env vars will still work via getenv() or $_ENV
RUN touch /var/www/html/.env && \
    chown -R www-data:www-data /var/www/html

# Optional: If your app needs write permissions
RUN chmod -R 755 /var/www/html