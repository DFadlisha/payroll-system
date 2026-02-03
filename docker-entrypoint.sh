#!/bin/bash
set -e

# Define port, default to 8080 if not set (standard for cloud containers)
# If Zeabur passes PORT, we use it.
PORT=${PORT:-8080}

echo "🚀 Starting application setup..."
echo "configured to listen on port: $PORT"

# Robustly update Apache port configuration using Regex to catch any existing number
if [ -f /etc/apache2/ports.conf ]; then
    echo "Updating ports.conf..."
    sed -i -E "s/Listen [0-9]+/Listen $PORT/g" /etc/apache2/ports.conf
fi

if [ -f /etc/apache2/sites-available/000-default.conf ]; then
    echo "Updating sites-available/000-default.conf..."
    sed -i -E "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:$PORT>/g" /etc/apache2/sites-available/000-default.conf
fi

# Add ServerName to prevent startup warnings
if ! grep -q "ServerName localhost" /etc/apache2/apache2.conf; then
    echo "ServerName localhost" >> /etc/apache2/apache2.conf
fi

# Verify configuration in logs
echo "--- VERIFY APACHE CONFIG ---"
grep "Listen" /etc/apache2/ports.conf
grep "VirtualHost" /etc/apache2/sites-available/000-default.conf
echo "----------------------------"

echo "✅ Awaiting incoming connections..."
exec apache2-foreground
