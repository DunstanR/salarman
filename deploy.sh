#!/bin/bash

# Deployment script for SALARMan
echo "Starting deployment process..."

# Create production .env file
echo "Creating production environment file..."
cat > .env.production << EOL
APP_NAME=SALARMan
APP_ENV=production
APP_DEBUG=false
APP_URL=https://salarman.netmaiesta.com

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=sqlite
DB_DATABASE=:memory:

MONGODB_URI=mongodb+srv://easytravelappnipun:nuLV9MTVegi8F3QS@easytravelapp.jcifyj4.mongodb.net/edhirya?authSource=admin&replicaSet=atlas-yg44qu-shard-0&w=majority&readPreference=primary&appname=MongoDB%20Compass&retryWrites=true&ssl=true
MONGODB_DATABASE=edhirya

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@salarman.netmaiesta.com"
MAIL_FROM_NAME="SALARMan"
EOL

# Install dependencies
echo "Installing dependencies..."
composer install --no-dev --optimize-autoloader

# Clear and cache configuration
echo "Optimizing application..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set proper permissions
echo "Setting permissions..."
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage/logs
chmod -R 775 storage/framework

echo "Deployment script completed!" 