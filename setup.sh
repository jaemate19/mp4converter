#!/bin/bash

echo "Setting up MP4 Converter environment..."

# Create jobs directory
mkdir -p jobs

# Set ownership and permissions
chown -R www-data:www-data jobs
chmod -R 775 jobs

echo "Basic permissions configured."

# Handle SELinux
if command -v getenforce &> /dev/null; then
    if [ "$(getenforce)" != "Disabled" ]; then
        echo "SELinux detected. Applying context..."
        chcon -R -t httpd_sys_rw_content_t jobs
    fi
fi

# AppArmor notice
if command -v aa-status &> /dev/null; then
    echo "AppArmor detected."
    echo "Ensure your project is inside /var/www/html or properly configured."
fi

echo "Setup complete."
