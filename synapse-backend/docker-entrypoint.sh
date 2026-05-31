#!/bin/bash
set -e

# Update Apache port
sed -i "s/__PORT__/$PORT/g" /etc/apache2/sites-available/000-default.conf
echo "Listen $PORT" > /etc/apache2/ports.conf

# Decode Firebase credentials
if [ -n "$FIREBASE_CREDENTIALS_BASE64" ]; then
    printf '%s' "$FIREBASE_CREDENTIALS_BASE64" | base64 -d > /var/www/html/storage/app/firebase-credentials.json
    echo "Firebase credentials written."
    echo "--- DEBUG firebase file ---"
    cat /var/www/html/storage/app/firebase-credentials.json | head -5
    echo "--- END DEBUG ---"
fi

# Cache Laravel config/routes/views
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec "$@"