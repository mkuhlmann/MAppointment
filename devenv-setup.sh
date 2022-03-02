#!/bin/bash

echo '🚧 Setting up development environment ...'

cd server

composer -v > /dev/null 2>&1
COMPOSER=$?
if [[ $COMPOSER -ne 0 ]]; then
	echo '🔃 Installing Composer...'
    cd server
	curl -sS https://getcomposer.org/installer | php
	COMPOSER=php composer.phar
	cd ..
else
	echo '✅ Composer already installed'
	COMPOSER=composer
fi


echo '🔃 Installing php dependencies ...'
$COMPOSER install
cd ..


echo '🔃 Installing node dependencies ...'
npm install


echo '✅ Development environment setup successfully.'
echo '🚀 Run `npm run dev` to start the development server.'