<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Firebase\JWT\JWT;
use Laminas\Diactoros\Response\JsonResponse;
use ParagonIE\EasyDB\EasyDB;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthMiddleware implements MiddlewareInterface
{
	private EasyDB $db;

	/**
	 * 
	 * 
	 */
	public function __construct(EasyDB $db)
	{
		$this->db = $db;
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		$jwt = $request->getHeaderLine('Authorization');

		if (empty($jwt)) {
			return new JsonResponse(['error' => 'missing authorization header'], 401);
		}

		try {
			$decoded = JWT::decode($jwt, $_ENV['JWT_SECRET'], ['HS256']);
		} catch (\Exception $e) {
			return new JsonResponse(['error' => 'invalid token'], 401);
		}

		$user = $this->db->row('SELECT * FROM users WHERE id = ?', $decoded->sub);

		if (empty($user)) {
			return new JsonResponse(['error' => 'invalid token'], 401);
		}

		$response = $handler->handle($request);
		return $response;
	}
}
