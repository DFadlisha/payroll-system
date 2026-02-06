FROM php:8.2-apache

# Install dependencies for PostgreSQL and other extensions
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    && docker-php-ext-install pdo pdo_pgsql pgsql zip

# Enable Apache mod_rewrite for nice URLs
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Configure Apache to use the PORT environment variable (Zeabur requirement)
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# Add a custom entrypoint script to handle startup
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Expose the port (default 80, but Zeabur might change it)
ENV PORT=80
EXPOSE ${PORT}

# Use the entrypoint script
ENTRYPOINT ["docker-entrypoint.sh"]
