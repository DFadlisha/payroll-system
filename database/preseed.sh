#!/bin/bash
# Pre-seed script to run during Docker build
# This ensures holidays are available immediately without API calls on first load

php /var/www/html/database/run_seed.php 2025
php /var/www/html/database/run_seed.php 2026

echo "✅ Pre-seeded holidays for 2025-2026"
