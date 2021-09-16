<?php declare(strict_types=1);

date_default_timezone_set('UTC');

include 'vendor/autoload.php';

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
	$app->get('dbManager')->migrate();
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
