#!/bin/bash

# Exit on error
set -e

echo "🚀 Starting deployment..."

# Load environment variables
if [ -f .env ]; then
    source .env
fi

# Pull latest changes
echo "📥 Pulling latest changes..."
git pull origin main

# Build and start containers
echo "🏗️ Building and starting containers..."
docker-compose -f docker-compose.prod.yml build
docker-compose -f docker-compose.prod.yml up -d

# Install dependencies
echo "📦 Installing dependencies..."
docker-compose -f docker-compose.prod.yml exec app composer install --no-dev --optimize-autoloader
docker-compose -f docker-compose.prod.yml exec app npm install --production

# Run database migrations
echo "🗄️ Running migrations..."
docker-compose -f docker-compose.prod.yml exec app php artisan migrate --force

# Clear and cache routes
echo "🔄 Clearing and caching..."
docker-compose -f docker-compose.prod.yml exec app php artisan config:cache
docker-compose -f docker-compose.prod.yml exec app php artisan route:cache
docker-compose -f docker-compose.prod.yml exec app php artisan view:cache
docker-compose -f docker-compose.prod.yml exec app php artisan optimize

# Set permissions
echo "🔒 Setting permissions..."
docker-compose -f docker-compose.prod.yml exec app chown -R www-data:www-data /var/www/tida-retail
docker-compose -f docker-compose.prod.yml exec app chmod -R 775 storage bootstrap/cache

# Restart queue workers
echo "🔄 Restarting queue workers..."
docker-compose -f docker-compose.prod.yml exec app php artisan queue:restart

# Clear application cache
echo "🧹 Clearing application cache..."
docker-compose -f docker-compose.prod.yml exec app php artisan cache:clear

# Check application status
echo "✅ Checking application status..."
docker-compose -f docker-compose.prod.yml exec app php artisan about

echo "🎉 Deployment completed successfully!" 