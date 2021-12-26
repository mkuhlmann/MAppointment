FROM node:alpine as build-stage
COPY . /app
WORKDIR /app/app
RUN npm install
RUN npm run build

FROM alpine:3.13 as production-stage

RUN apk add --no-cache nginx git openrc zip openssh curl

RUN apk add --no-cache \
  php81-common php81 php81-curl php81-fileinfo php81-fpm php81-gd php81-iconv php81-intl php81-mbstring \
  php81-opcache php81-openssl php81-pdo php81-pdo_sqlite php81-phar

RUN curl -sS https://getcomposer.org/installer | php81 -- --install-dir=/usr/local/bin --filename=composer

# make sure php runs with same uid as nginx
RUN sed -i 's/user = .*/user = nginx/g' /etc/php81/php-fpm.d/www.conf && \
  sed -i 's/group = .*/group = nginx/g' /etc/php81/php-fpm.d/www.conf && \
  sed -i 's/;listen.owner = .*/listen.owner = nginx/g' /etc/php81/php-fpm.d/www.conf && \
  sed -i 's/;listen.group = .*/listen.group = nginx/g' /etc/php81/php-fpm.d/www.conf && \
  sed -i 's/listen = 127.*/listen = \/var\/run\/php-fpm.sock/g' /etc/php81/php-fpm.d/www.conf && \
  sed -i 's/upload_max_filesize = .*/upload_max_filesize = 50M/g' /etc/php81/php.ini && \
  sed -i 's/post_max_size = .*/post_max_size = 50M/g' /etc/php81/php.ini

COPY ./docker/site.conf /etc/nginx/conf.d/default.conf
COPY ./docker/entrypoint.sh /etc/entrypoint.sh

RUN chmod +x /etc/entrypoint.sh
RUN mkdir -p /run/nginx

COPY --from=build-stage --chown nginx.nginx /app/server /app
WORKDIR /app

EXPOSE 80
CMD