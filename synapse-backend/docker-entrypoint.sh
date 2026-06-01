#!/bin/bash
set -e

# Clear compiled files
php artisan clear-compiled 2>/dev/null || true
php artisan optimize:clear 2>/dev/null || true

# Update Apache port
sed -i "s/__PORT__/$PORT/g" /etc/apache2/sites-available/000-default.conf
echo "Listen $PORT" > /etc/apache2/ports.conf

# Fix MPM
a2dismod mpm_event 2>/dev/null || true
a2enmod mpm_prefork 2>/dev/null || true

# Decode Firebase credentials
if [ -n "$FIREBASE_CREDENTIALS_BASE64" ]; then
    printf '%s' "$FIREBASE_CREDENTIALS_BASE64" | base64 -d > /var/www/html/storage/app/firebase-credentials.json
    echo "Firebase credentials written."
fi

# Set permissions
chown -R www-data:www-data /var/www/html/storage
chmod -R 775 /var/www/html/storage

# Buat folder untuk compiled views
mkdir -p /tmp/views
chmod -R 777 /tmp/views

# Clear and recache Laravel config
php artisan config:clear || echo "config:clear failed, continuing..."
php artisan cache:clear || echo "cache:clear failed, continuing..."
php artisan config:cache || echo "config:cache failed, continuing..."
php artisan route:cache || echo "route:cache failed, continuing..."

exec "$@"