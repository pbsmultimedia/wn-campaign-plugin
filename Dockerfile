# Extend the WinterCMS base image
FROM ghcr.io/mpo-web-consulting/wintercms:latest

# Install system dependencies and PHP extensions
# this is needed for the mall plugin
RUN apt-get update && apt-get install -y \
    libgmp-dev \
    && docker-php-ext-install gmp bcmath

# Clean up to reduce image size
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Fix permissions for storage and cache
# RUN chown -R www-data:www-data /var/www/html/storage/logs && chmod -R 2775 /var/www/html/storage/logs
