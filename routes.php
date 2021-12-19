<?php declare(strict_types=1);
namespace App;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AppointmentController;
use App\Http\Middleware\AuthMiddleware;
use App\Http\Middleware\CorsMiddleware;
use App\Http\Middleware\JsonPayloadMiddleware;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\TextResponse;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/** @global \League\Route\Router $router */
global $router;
/** @global \App\Application */
global $app;

$auth = new AuthMiddleware($app->get('db'));

$router->middleware(new JsonPayloadMiddleware());

$router->group('/api/v1', function(\League\Route\RouteGroup $route) use ($auth) {
	$route->options('/{any:.*}', function(ServerRequestInterface $request): ResponseInterface {
		return (new Response())->withAddedHeader('Access-Control-Allow-Methods', 'POST, GET, PATCH, PUT, OPTIONS')->withAddedHeader('Access-Control-Allow-Headers', 'Content-Type, Accept, x-requested-with');
	});

	$route->get('/appointments', [ AppointmentController::class, 'getAppointments'])->middleware($auth);
	
	$route->post('/auth/login', [AuthController::class, 'login']);

	$route->get('/appointments/{id}', [AppointmentController::class, 'getAppointment']);
	$route->get('/appointments/{id}/get-available-dates', [AppointmentController::class, 'getAvailableDates']);
	$route->get('/appointments/{id}/get-available-slots/{date}', [AppointmentController::class, 'getAvailableSlots']);

	$route->post('/debug', function(ServerRequestInterface $request): ResponseInterface {
		
		return new TextResponse(print_r($request->getBody()->__toString(), true));
	});

})->middleware(new CorsMiddleware());

$router->map('GET', '/admin/{any:.*}', function(ServerRequestInterface $request): ResponseInterface {
	return new HtmlResponse(file_get_contents(__DIR__ . '/public/admin/index.html'));
});