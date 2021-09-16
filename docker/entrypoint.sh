#!/bin/sh

# fix permissions if needed
chown nginx.nginx -R /www
if [ -d /data ]; then
  chown nginx.nginx -R /data
fi

nginx
php-fpm8 -F