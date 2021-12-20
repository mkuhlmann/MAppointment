FROM node:alpine as build-stage
COPY . /app
WORKDIR /app/app
RUN npm install
RUN npm run build

FROM alpine:3.13 as production-stage

RUN apk add --no-cache nginx git openrc zip openssh curl

RUN apk add --no-cache \
  php8-common php8-cli php8-curl php8-fileinfo php8-fpm php8-gd php8-iconv php8-intl php8-json php8-mbstring \
  php8-opcache php8-openssl php8-pdo php8-pdo_sqlite php8-phar

RUN curl -sS https://getcomposer.org/installer | php8 -- --install-dir=/usr/local/bin --filename=composer

# make sure php runs with same uid as nginx
RUN sed -i 's/user = .*/user = nginx/g' /etc/php8/php-fpm.d/www.conf && \
  sed -i 's/group = .*/group = nginx/g' /etc/php8/php-fpm.d/www.conf && \
  sed -i 's/;listen.owner = .*/listen.owner = nginx/g' /etc/php8/php-fpm.d/www.conf && \
  sed -i 's/;listen.group = .*/listen.group = nginx/g' /etc/php8/php-fpm.d/www.conf && \
  sed -i 's/listen = 127.*/listen = \/var\/run\/php-fpm.sock/g' /etc/php8/php-fpm.d/www.conf && \
  sed -i 's/upload_max_filesize = .*/upload_max_filesize = 50M/g' /etc/php8/php.ini && \
  sed -i 's/post_max_size = .*/post_max_size = 50M/g' /etc/php8/php.ini

COPY ./docker/site.conf /etc/nginx/conf.d/default.conf
COPY ./docker/entrypoint.sh /etc/entrypoint.sh

RUN chmod +x /etc/entrypoint.sh
RUN mkdir -p /run/nginx

COPY --from=build-stage --chown nginx.nginx /app /app
WORKDIR /app

EXPOSE 80
CMD