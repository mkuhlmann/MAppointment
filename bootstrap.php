<?php declare(strict_types=1);

use App\Helper;

date_default_timezone_set('UTC');

include 'vendor/autoload.php';
include 'helpers.php';

/** Application and DI Setup */
$container = new \League\Container\Container();
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
	->add(\App\Config::class)
	->addArgument([
		$app->path('config.php'),
		$app->path('data/config.php')
	])
	->addTag('config')
	->setShared(true);

$container
	->add(\App\Db\DatabaseManager::class)
	->addTag('dbManager')
	->addArguments([$app->get('config'), $app->get('app')])
	->setShared(true);

$container
	->add(\ParagonIE\EasyDB\EasyDB::class, $app->get('dbManager')->getDb())
	->addTag('db')
	->setShared(true);


if(php_sapi_name() == 'cli') {
	/** CLI */

	/** @var \ParagonIE\EasyDB\EasyDB $db */
	$db = $app->get('db');

	$db->exec('DROP TABLE appointment_slot_bookings; DROP TABLE appointment_slots; DROP TABLE appointments; DROP TABLE users; DELETE FROM _migrations;');

	$app->get('dbManager')->migrate();
	$db->insert('users', [
		'id' => Helper::nanoid(),
		'username' => 'mkuhlmann',
		'email' => 'manuel@mkuhlmann.org',
		'password' => password_hash('qwertz', PASSWORD_ARGON2ID)
	]);
	$id = 'test';
	$db->insert('appointments', [
		'id' => $id,
		'name' => 'Grippeschutzimpfung 2021 Aekno'
	]);

	for($i = 10; $i < 18; $i++) {
		$db->insert('appointment_slots', [
			'id' => Helper::nanoid(),
			'slots' => 2,
			'free' => 2,
			'date' => date('Y-m-d ' . $i . ':i:s', strtotime('2021-09-20 10:00:00')),
			'duration' => 30,
			'appointment_id' => $id,
			'created_at' => \dbdate(),
			'updated_at' => \dbdate()
		]);
 	}

	 for($i = 10; $i < 18; $i++) {
		$db->insert('appointment_slots', [
			'id' => Helper::nanoid(),
			'slots' => 2,
			'free' => 0,
			'date' => date('Y-m-d ' . $i . ':i:s', strtotime('2021-09-21 10:00:00')),
			'duration' => 30,
			'appointment_id' => $id,
			'created_at' => \dbdate(),
			'updated_at' => \dbdate()
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
		$_SERVER, $_GET, $_POST, $_COOKIE, $_FILES
	);

	/** Let's go. */
	$response = $router->dispatch($request);
	(new Laminas\HttpHandlerRunner\Emitter\SapiEmitter())->emit($response);
}