# Tida Retail Backend Cloud Deployment Guide

## Table of Contents
1. [Prerequisites](#prerequisites)
2. [Choosing a Cloud Provider](#choosing-a-cloud-provider)
3. [Setting Up the Server](#setting-up-the-server)
4. [Deploying the Application](#deploying-the-application)
5. [Configuring the Database](#configuring-the-database)
6. [Setting Up SSL](#setting-up-ssl)
7. [Making the API Accessible](#making-the-api-accessible)
8. [Connecting the Frontend](#connecting-the-frontend)
9. [Monitoring and Maintenance](#monitoring-and-maintenance)

## Prerequisites

Before you start, make sure you have:
- A code editor (like VS Code)
- Git installed on your computer
- A cloud provider account (AWS, DigitalOcean, etc.)
- A domain name (optional but recommended)
- Basic knowledge of the command line

## Choosing a Cloud Provider

### Popular Options:
1. **DigitalOcean** (Recommended for beginners)
   - Simple interface
   - Good documentation
   - Affordable pricing
   - One-click Laravel setup

2. **AWS** (More advanced)
   - More features
   - More complex setup
   - More expensive
   - Better for scaling

3. **Google Cloud** (Balanced)
   - Good free tier
   - Reliable performance
   - Moderate complexity

## Setting Up the Server

### 1. Create a Droplet/Instance
- Choose Ubuntu 22.04 LTS
- Select at least 2GB RAM
- Choose a region close to your users
- Enable backups (recommended)

### 2. Connect to Your Server
```bash
# Replace IP_ADDRESS with your server's IP
ssh root@IP_ADDRESS
```

### 3. Basic Server Setup
```bash
# Update system
sudo apt update
sudo apt upgrade -y

# Install required software
sudo apt install -y nginx mysql-server php8.2-fpm php8.2-mysql php8.2-xml php8.2-curl php8.2-mbstring php8.2-zip composer redis-server
```

## Deploying the Application

### 1. Set Up the Application Directory
```bash
# Create directory
sudo mkdir -p /var/www/tida-retail
sudo chown -R $USER:$USER /var/www/tida-retail

# Clone your repository
cd /var/www/tida-retail
git clone YOUR_REPOSITORY_URL .
```

### 2. Install Dependencies
```bash
# Install Composer dependencies
composer install --no-dev --optimize-autoloader

# Set up environment
cp .env.example .env
php artisan key:generate
```

### 3. Configure Nginx
Create a new file at `/etc/nginx/sites-available/tida-retail`:
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/tida-retail/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable the site:
```bash
sudo ln -s /etc/nginx/sites-available/tida-retail /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

## Configuring the Database

### 1. Set Up MySQL
```bash
sudo mysql_secure_installation
```

### 2. Create Database and User
```bash
sudo mysql
```
```sql
CREATE DATABASE tida_retail;
CREATE USER 'tida_user'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON tida_retail.* TO 'tida_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 3. Update Environment File
Edit `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tida_retail
DB_USERNAME=tida_user
DB_PASSWORD=your_password
```

### 4. Run Migrations
```bash
php artisan migrate
php artisan db:seed
```

## Setting Up SSL

### 1. Install Certbot
```bash
sudo apt install certbot python3-certbot-nginx
```

### 2. Get SSL Certificate
```bash
sudo certbot --nginx -d your-domain.com
```

## Making the API Accessible

### 1. Configure CORS
Edit `config/cors.php`:
```php
return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'], // Replace with your frontend domain
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
```

### 2. Set Up API Routes
Your API routes are already configured in `routes/api.php`. They will be accessible at:
```
https://your-domain.com/api/v1/...
```

## Connecting the Frontend

### 1. Set Up Environment Variables
Create a `.env` file in your frontend project:
```env
VITE_API_URL=https://your-domain.com/api/v1
VITE_APP_NAME=TidaRetail
```

### 2. Configure API Client
Example using Axios:
```javascript
import axios from 'axios';

const api = axios.create({
    baseURL: import.meta.env.VITE_API_URL,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
});

// Add request interceptor for authentication
api.interceptors.request.use(config => {
    const token = localStorage.getItem('token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

export default api;
```

### 3. Make API Calls
Example:
```javascript
import api from './api';

// Get products
const getProducts = async () => {
    try {
        const response = await api.get('/products');
        return response.data;
    } catch (error) {
        console.error('Error fetching products:', error);
        throw error;
    }
};
```

## Monitoring and Maintenance

### 1. Set Up Monitoring
```bash
# Install monitoring tools
sudo apt install prometheus node-exporter
```

### 2. Configure Backups
```bash
# Make backup script executable
chmod +x scripts/backup.sh

# Add to crontab
crontab -e
```
Add this line:
```bash
0 2 * * * /var/www/tida-retail/scripts/backup.sh
```

### 3. Regular Maintenance
```bash
# Update system
sudo apt update
sudo apt upgrade -y

# Update Composer dependencies
composer update

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

## Troubleshooting

### Common Issues:
1. **502 Bad Gateway**
   - Check PHP-FPM status: `sudo systemctl status php8.2-fpm`
   - Check Nginx error logs: `sudo tail -f /var/log/nginx/error.log`

2. **Database Connection Issues**
   - Verify database credentials in `.env`
   - Check MySQL status: `sudo systemctl status mysql`

3. **API Not Accessible**
   - Check CORS configuration
   - Verify Nginx configuration
   - Check firewall settings

### Getting Help:
- Check server logs: `sudo tail -f /var/log/nginx/error.log`
- Check application logs: `tail -f storage/logs/laravel.log`
- Search online for error messages
- Contact your cloud provider's support

## Next Steps
1. Set up automated deployments
2. Configure load balancing
3. Implement caching
4. Set up monitoring alerts
5. Create a staging environment 