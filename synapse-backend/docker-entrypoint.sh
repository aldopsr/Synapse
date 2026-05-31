#!/bin/bash
set -e

# Update Apache port to use Render's dynamic PORT
sed -i "s/__PORT__/$PORT/g" /etc/apache2/sites-available/000-default.conf
echo "Listen $PORT" > /etc/apache2/ports.conf

# Decode Firebase credentials from base64 env variable
if [ -n "$FIREBASE_CREDENTIALS_BASE64" ]; then
    echo "$FIREBASE_CREDENTIALS_BASE64" | base64 -d > /var/www/html/storage/app/firebase-credentials.json
    echo "Firebase credentials written."
fi

# Cache Laravel config/routes/views for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec "$@"
