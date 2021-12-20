<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CorsMiddleware implements MiddlewareInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{				
		$response = $handler->handle($request);

		$response = $response->withAddedHeader('Access-Control-Allow-Origin', '*');
		$response = $response->withAddedHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
		$response = $response->withAddedHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Authorization');

		return $response;
	}
}
