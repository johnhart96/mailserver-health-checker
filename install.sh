#!/bin/bash

apt update
apt install php php-imap php-cli unzip
curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php
php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer
rm /tmp/composer-setup.php
composer install
nano health-check.php

# Add a cron job to run health-check.php every hour
(crontab -l 2>/dev/null; echo "0 * * * * /usr/bin/php $(pwd)/health-check.php >> $(pwd)/monitor.log 2>&1") | crontab -