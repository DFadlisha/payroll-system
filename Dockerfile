# Multi-stage build for optimized image size
FROM composer:latest AS composer-build
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --prefer-dist

FROM php:8.2-apache

# Install system dependencies in a single layer
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpq-dev \
    libzip-dev \
    curl \
    && docker-php-ext-install -j$(nproc) pdo pdo_pgsql pgsql zip opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache modules
RUN a2enmod rewrite headers expires deflate

# Configure PHP for production
RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.memory_consumption=128'; \
    echo 'opcache.interned_strings_buffer=8'; \
    echo 'opcache.max_accelerated_files=10000'; \
    echo 'opcache.revalidate_freq=2'; \
    echo 'opcache.fast_shutdown=1'; \
    echo 'realpath_cache_size=4096K'; \
    echo 'realpath_cache_ttl=600'; \
    } > /usr/local/etc/php/conf.d/opcache.ini

# Set working directory
WORKDIR /var/www/html

# Copy vendor from composer build stage
COPY --from=composer-build /app/vendor ./vendor

# Copy application files
COPY --chown=www-data:www-data . .

# Set permissions
RUN chmod -R 755 /var/www/html && \
    find /var/www/html -type f -exec chmod 644 {} \; && \
    find /var/www/html -type d -exec chmod 755 {} \;

# Create optimized Apache configuration
RUN echo '<Directory /var/www/html>\n\
    Options -Indexes +FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
    </Directory>\n\
    <IfModule mod_deflate.c>\n\
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript\n\
    </IfModule>' > /etc/apache2/conf-available/payroll.conf \
    && a2enconf payroll

# Expose port
EXPOSE 80

# Optimized health check
HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD curl -f http://localhost:${PORT:-80}/health.php || exit 1

# Create startup script for better error handling
# Create startup script using printf to ensure correct newlines and avoid echo compatibility issues
RUN printf "#!/bin/bash\n" > /start.sh && \
    printf "set -e\n" >> /start.sh && \
    printf "PORT=\${PORT:-80}\n" >> /start.sh && \
    printf "echo \"Starting Apache on port \$PORT...\"\n" >> /start.sh && \
    printf "sed -i \"s/Listen 80/Listen \$PORT/g\" /etc/apache2/ports.conf\n" >> /start.sh && \
    printf "sed -i \"s/<VirtualHost *:80>/<VirtualHost *:\$PORT>/g\" /etc/apache2/sites-available/000-default.conf\n" >> /start.sh && \
    printf "echo \"ServerName localhost\" >> /etc/apache2/apache2.conf\n" >> /start.sh && \
    printf "echo \"Apache configured successfully\"\n" >> /start.sh && \
    printf "exec apache2-foreground\n" >> /start.sh && \
    chmod +x /start.sh

CMD ["/start.sh"]
