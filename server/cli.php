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

$container->addServiceProvider(new \App\ServiceProviders\ApplicationServiceProvider());
$container->addServiceProvider(new \App\ServiceProviders\RouteServiceProvider());

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
	$db->insert('users', [
		'id' => Helper::nanoid(),
		'username' => 'mkuhlmann',
		'email' => 'manuel@mkuhlmann.org',
		'password' => password_hash('qwertz', PASSWORD_ARGON2ID)
	]);

	$id = 'test';
	$db->insert('appointments', [
		'id' => $id,
		'name' => 'Grippeschutzimpfung 2021 Aekno',
		'locationLat' => 51.224150,
		'locationLng' => 6.864700,
	]);

	for ($i = 10; $i < 18; $i++) {
		$db->insert('slots', [
			'id' => Helper::nanoid(),
			'slots' => 2,
			'free' => 2,
			'start' => date('Y-m-d ' . $i . ':i:s', strtotime('2021-12-29 10:00:00')),
			'end' => date('Y-m-d ' . $i . ':i:s', strtotime('2021-12-29 10:30:00')),
			'appointmentId' => $id,
			'createdAt' => \dbdate(),
			'updatedAt' => \dbdate()
		]);
	}

	for ($i = 10; $i < 18; $i++) {
		$db->insert('slots', [
			'id' => Helper::nanoid(),
			'slots' => 2,
			'free' => mt_rand(0, 2),
			'start' => date('Y-m-d ' . $i . ':i:s', strtotime('2021-12-30 10:00:00')),
			'end' => date('Y-m-d ' . $i . ':i:s', strtotime('2021-12-30 10:30:00')),
			'appointmentId' => $id,
			'createdAt' => \dbdate(),
			'updatedAt' => \dbdate()
		]);
	}
}