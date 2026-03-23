#!/bin/bash

JOBS_DIR="/var/www/html/jobs"

# Delete directories older than 1 hour
if [ -d "$JOBS_DIR" ] && [[ "$JOBS_DIR" == /var/www/html/jobs ]]; then
    find "$JOBS_DIR" -mindepth 1 -maxdepth 1 -type d -mmin +60 -exec rm -rf {} +
else
    echo "Invalid JOBS_DIR"
fi

echo "Cleanup run at $(date)"