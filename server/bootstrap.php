<?php

declare(strict_types=1);

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
$router = $container->get(\League\Route\Router::class);

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
