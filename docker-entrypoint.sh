#!/bin/bash
set -e

# Define port, default to 80 if not set
PORT=${PORT:-80}

echo "🚀 Starting application setup on port $PORT..."

# Update Apache port configuration
echo "🔧 Configuring Apache to listen on port $PORT..."
sed -i "s/Listen 80/Listen $PORT/g" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:$PORT>/g" /etc/apache2/sites-available/000-default.conf

# Add ServerName to prevent warnings
echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Start Apache in foreground
echo "✅ Apache configured successfully. Starting server..."
exec apache2-foreground
