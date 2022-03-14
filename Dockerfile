FROM node:alpine as build-stage
COPY ./app /app/app
WORKDIR /app/app
RUN npm install
RUN npm run build

FROM alpine:3.15 as production-stage

RUN apk add --no-cache nginx git openrc zip openssh curl

RUN apk add --no-cache \
  php8-common php8 php8-curl php8-fileinfo php8-fpm php8-gd php8-iconv php8-intl php8-mbstring \
  php8-ctype php8-opcache php8-openssl php8-pdo php8-pdo_sqlite php8-pdo_mysql php8-phar \
	&& curl -sS https://getcomposer.org/installer | php8 -- --install-dir=/usr/local/bin --filename=composer

# make sure php runs with same uid as nginx
RUN sed -i 's/user = .*/user = nginx/g' /etc/php8/php-fpm.d/www.conf && \
  sed -i 's/group = .*/group = nginx/g' /etc/php8/php-fpm.d/www.conf && \
  sed -i 's/;listen.owner = .*/listen.owner = nginx/g' /etc/php8/php-fpm.d/www.conf && \
  sed -i 's/;listen.group = .*/listen.group = nginx/g' /etc/php8/php-fpm.d/www.conf && \
  sed -i 's/;clear_env = .*/clear_env = no/g' /etc/php8/php-fpm.d/www.conf && \
  sed -i 's/listen = 127.*/listen = \/var\/run\/php-fpm.sock/g' /etc/php8/php-fpm.d/www.conf && \
  sed -i 's/upload_max_filesize = .*/upload_max_filesize = 50M/g' /etc/php8/php.ini && \
  sed -i 's/post_max_size = .*/post_max_size = 50M/g' /etc/php8/php.ini && \
	sed -i 's/variables_order = .*/variables_order = "EGPCS"/g' /etc/php8/php.ini

COPY ./docker/site.conf /etc/nginx/http.d/default.conf
COPY ./docker/entrypoint.sh /entrypoint.sh

RUN chmod +x /entrypoint.sh
RUN mkdir -p /run/nginx

COPY ./server /app/server
COPY --from=build-stage --chown=nginx:nginx /app/server /app/server
WORKDIR /app/server
RUN php8 /usr/local/bin/composer install

EXPOSE 80
ENTRYPOINT ["/entrypoint.sh"]