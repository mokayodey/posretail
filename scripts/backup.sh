#!/bin/bash

# Set variables
BACKUP_DIR="/var/www/tida-retail/backups"
DATE=$(date +%Y%m%d_%H%M%S)
DB_HOST="winter-field.pleasant-way-production.svc.pipeops.internal"
DB_USER="pipeops_user"
DB_PASS="f8182a4a855070972a7f16529"
DB_NAME="pipeops"

# Create backup directory if it doesn't exist
mkdir -p $BACKUP_DIR

# Create database backup
mysqldump -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME > $BACKUP_DIR/db_backup_$DATE.sql

# Compress the backup
gzip $BACKUP_DIR/db_backup_$DATE.sql

# Backup application files
tar -czf $BACKUP_DIR/app_backup_$DATE.tar.gz /var/www/tida-retail

# Remove backups older than 7 days
find $BACKUP_DIR -type f -mtime +7 -delete

# Log the backup
echo "Backup completed at $(date)" >> $BACKUP_DIR/backup.log

# Sync to remote storage (if configured)
if [ -f "/var/www/tida-retail/.env" ]; then
    source /var/www/tida-retail/.env
    if [ ! -z "$AWS_ACCESS_KEY_ID" ] && [ ! -z "$AWS_SECRET_ACCESS_KEY" ]; then
        echo "Syncing backups to S3..."
        aws s3 sync $BACKUP_DIR s3://$AWS_BUCKET/backups/
    fi
fi

echo "Backup completed successfully!" 