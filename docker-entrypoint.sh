#!/bin/bash
set -e

# Default PORT to 80 if not set
export PORT=${PORT:-80}

# Update Apache port configuration dynamically based on PORT env var
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/g" /etc/apache2/sites-available/000-default.conf

# Start Apache in foreground
exec apache2-foreground
