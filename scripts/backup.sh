#!/bin/bash

# Configuration
BACKUP_DIR="/var/backups/tida-retail"
DB_USER="your-db-user"
DB_PASS="your-db-password"
DB_NAME="tida_retail"
APP_DIR="/var/www/tida-retail"
DATE=$(date +%Y%m%d_%H%M%S)

# Create backup directory if it doesn't exist
mkdir -p $BACKUP_DIR

# Database backup
echo "Creating database backup..."
mysqldump -u$DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_backup_$DATE.sql.gz

# Application files backup
echo "Creating application files backup..."
tar -czf $BACKUP_DIR/app_backup_$DATE.tar.gz $APP_DIR

# Cleanup old backups (keep last 7 days)
find $BACKUP_DIR -type f -mtime +7 -delete

# Sync to remote storage (if configured)
if [ -f "$APP_DIR/.env" ]; then
    source $APP_DIR/.env
    if [ ! -z "$AWS_ACCESS_KEY_ID" ] && [ ! -z "$AWS_SECRET_ACCESS_KEY" ]; then
        echo "Syncing backups to S3..."
        aws s3 sync $BACKUP_DIR s3://$AWS_BUCKET/backups/
    fi
fi

echo "Backup completed successfully!" 