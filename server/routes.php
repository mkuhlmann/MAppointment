<?php

declare(strict_types=1);

/** @var \League\Route\Router $router */
/** @var \League\Container\Container $container */

namespace App;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\SlotController;
use App\Http\Middleware\AuthMiddleware;
use App\Http\Middleware\CorsMiddleware;
use App\Http\Middleware\JsonPayloadMiddleware;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\TextResponse;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;


$auth = $container->getNew(AuthMiddleware::class);

$router->middleware(new JsonPayloadMiddleware());

$router->group('/api/v1', function (\League\Route\RouteGroup $route) use ($auth) {
	$route->options('/{any:.*}', function (ServerRequestInterface $request): ResponseInterface {
		return new Response();
	});

	$route->post('/auth/login', [AuthController::class, 'login']);
	$route->get('/auth/user', [AuthController::class, 'getUser'])->middleware($auth);

	// /appointments admin routes
	$route->get('/appointments', [AppointmentController::class, 'getAppointments'])->middleware($auth);
	$route->post('/appointments', [AppointmentController::class, 'createAppointment'])->middleware($auth);
	$route->put('/appointments/{id}', [AppointmentController::class, 'updateAppointment'])->middleware($auth);
	$route->get('/appointments/{id}/bookings', [AppointmentController::class, 'getBookings'])->middleware($auth);
	
	$route->post('/appointments/{id}/slots', [AppointmentController::class, 'createSlot'])->middleware($auth);

	// /appointments public routes
	$route->get('/appointments/{id}', [AppointmentController::class, 'getAppointment']);
	$route->get('/appointments/{id}/available-dates', [AppointmentController::class, 'getAvailableDates']);
	$route->get('/appointments/{id}/available-slots/{date}', [AppointmentController::class, 'getAvailableSlots']);

	$route->get('/appointments/{id}/slots', [AppointmentController::class, 'getSlots']);
	
	$route->get('/slots/{id}', [SlotController::class, 'getSlot']);
	$route->get('/slots/{id}/bookings', [SlotController::class, 'getBookings'])->middleware($auth);

	$route->put('/slots/{id}', [SlotController::class, 'updateSlot'])->middleware($auth);

	$route->post('/bookings', [BookingController::class, 'bookAppointment']);
	$route->get('/bookings/latest', [BookingController::class, 'getLatestBookings'])->middleware($auth);
	$route->get('/bookings/stats', [BookingController::class, 'getBookingStats'])->middleware($auth);
	$route->get('/bookings/{id}', [BookingController::class, 'getBooking']);

})->middleware(new CorsMiddleware());

$router->map('GET', '/admin/{any:.*}', function (ServerRequestInterface $request): ResponseInterface {
	return new HtmlResponse(file_get_contents(__DIR__ . '/public/admin/index.html'));
});
