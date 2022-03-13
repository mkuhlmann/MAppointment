<?php

declare(strict_types=1);

date_default_timezone_set('UTC');

require 'vendor/autoload.php';
require 'helpers.php';

/** .dotenv */
$dotenv = \Dotenv\Dotenv::createImmutable(dirname(__FILE__) . '/..');
$dotenv->safeLoad();

/** Application and DI Setup */
$container = new \App\Container();
$container->delegate(new League\Container\ReflectionContainer(true));
$container->add('baseDir', dirname(__FILE__));

$container
	->add(\League\Container\Container::class, $container)
	->setShared(true);

$container
	->add(\Psr\Container\ContainerInterface::class, $container)
	->setShared(true);

$container->addServiceProvider(new \App\Providers\ApplicationServiceProvider());
$container->addServiceProvider(new \App\Providers\RouteServiceProvider());
$container->addServiceProvider(new \App\Providers\MailServiceProvider());
