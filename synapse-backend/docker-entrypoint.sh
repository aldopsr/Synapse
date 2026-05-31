#!/bin/bash
set -e

# Update Apache port
sed -i "s/__PORT__/$PORT/g" /etc/apache2/sites-available/000-default.conf
echo "Listen $PORT" > /etc/apache2/ports.conf

# Decode Firebase credentials
if [ -n "$FIREBASE_CREDENTIALS_BASE64" ]; then
    printf '%s' "$FIREBASE_CREDENTIALS_BASE64" | base64 -d > /var/www/html/storage/app/firebase-credentials.json
    echo "Firebase credentials written."
fi

# Cache Laravel config/routes/views (non-fatal)
php artisan config:cache || echo "config:cache failed, continuing..."
php artisan route:cache || echo "route:cache failed, continuing..."
php artisan view:cache || echo "view:cache failed, continuing..."

exec "$@"