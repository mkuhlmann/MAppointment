<?php

declare(strict_types=1);

use App\Container;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;

require 'bootstrap.php';
/** @var $container Container */

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
	$response = new HtmlResponse(file_get_contents(__DIR__ . '/public/index.html'));
} catch (\Exception $e) {
	if($_ENV['ENVIRONMENT'] == 'development') {
		throw $e;
	}
	$response = new JsonResponse(['error' => $e->getMessage()]);
} finally {
	(new Laminas\HttpHandlerRunner\Emitter\SapiEmitter())->emit($response);
}

