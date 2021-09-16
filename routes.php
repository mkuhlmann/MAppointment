<?php declare(strict_types=1);
namespace App;

use App\Http\Controllers\AuthController;
use App\Http\Middleware\CorsMiddleware;

/** @global \League\Route\Router $router */
global $router;

$router->group('/api/v1', function(\League\Route\RouteGroup $route) {
	$route->get('/auth/login', [AuthController::class, 'login']);

})->middleware(new CorsMiddleware());