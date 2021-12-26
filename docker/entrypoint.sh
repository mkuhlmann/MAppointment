#!/bin/sh

# fix permissions if needed
chown nginx.nginx -R /app

nginx
php-fpm8 -F