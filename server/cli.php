<?php

declare(strict_types=1);

use App\Helper;

date_default_timezone_set('UTC');

include 'vendor/autoload.php';
include 'helpers.php';

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

$container->addServiceProvider(new \App\ServiceProvider\ApplicationServiceProvider());
$container->addServiceProvider(new \App\ServiceProvider\RouteServiceProvider());

$app = $container->get(\App\Application::class);

if (php_sapi_name() == 'cli') {
	/** CLI */

	/** @var \ParagonIE\EasyDB\EasyDB $db */
	$db = $app->get('db');

	
	echo 'Clearing database ...'.PHP_EOL;
	$db->exec('DROP TABLE IF EXISTS bookings; DROP TABLE IF EXISTS slots; DROP TABLE IF EXISTS appointments; DROP TABLE IF EXISTS users; DELETE FROM _migrations;');

	echo 'Migrating ...'.PHP_EOL;
	$app->get('dbManager')->migrate();

	echo 'Seeding ...'.PHP_EOL;


	$id = 'test';
	$db->insert('appointments', [
		'id' => $id,
		'name' => 'Grippeschutzimpfung 2021 Aekno',
		'latitude' => 51.224150,
		'longitude' => 6.864700,
	]);

}