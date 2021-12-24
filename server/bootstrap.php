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
$container->delegate(new League\Container\ReflectionContainer());

$app = new \App\Application(dirname(__FILE__), $container);

$container
	->add(\League\Container\Container::class, $container)
	->setAlias(\Psr\Container\ContainerInterface::class)
	->setShared(true);

$container
	->add(\App\Application::class, $app)
	->addTag('app')
	->setShared(true);

$container
	->add(\App\Db\DatabaseManager::class)
	->addTag('dbManager')
	->addArguments([$app])
	->setShared(true);

$container
	->add(\ParagonIE\EasyDB\EasyDB::class, $app->get('dbManager')->getDb())
	->addTag('db')
	->setShared(true);


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
			'date' => date('Y-m-d ' . $i . ':i:s', strtotime('2021-12-20 10:00:00')),
			'duration' => 30,
			'appointmentId' => $id,
			'createdAt' => \dbdate(),
			'updatedAt' => \dbdate()
		]);
	}

	for ($i = 10; $i < 18; $i++) {
		$db->insert('slots', [
			'id' => Helper::nanoid(),
			'slots' => 2,
			'free' => 0,
			'date' => date('Y-m-d ' . $i . ':i:s', strtotime('2021-12-21 10:00:00')),
			'duration' => 30,
			'appointmentId' => $id,
			'createdAt' => \dbdate(),
			'updatedAt' => \dbdate()
		]);
	}
} else {
	/** HTTP and Routing */
	$routerStrategy = new \League\Route\Strategy\ApplicationStrategy();
	$routerStrategy->setContainer($container);

	$router = new \League\Route\Router();
	$router->setStrategy($routerStrategy);

	include 'routes.php';

	$request = Laminas\Diactoros\ServerRequestFactory::fromGlobals(
		$_SERVER,
		$_GET,
		$_POST,
		$_COOKIE,
		$_FILES
	);

	/** Let's go. */
	try {
		$response = $router->dispatch($request);
	} catch (\League\Route\Http\Exception\NotFoundException $e) {
		$response = new \Laminas\Diactoros\Response\HtmlResponse(file_get_contents(__DIR__ . '/public/index.html'));
	}
	
	(new Laminas\HttpHandlerRunner\Emitter\SapiEmitter())->emit($response);
}
