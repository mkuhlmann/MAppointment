#!/bin/ash

# fix permissions if needed
chown nginx.nginx -R /app

php /app/server/cli.php migrate

echo Starting nginx ...
nginx
echo Starting php8-fpm
php-fpm8 -F
