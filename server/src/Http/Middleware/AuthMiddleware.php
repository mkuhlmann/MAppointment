<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Application;
use App\Models\User;
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
	private Application $app;

	public function __construct(EasyDB $db, Application $app)
	{
		$this->db = $db;
		$this->app = $app;
	}

	 /**
	 * Auth Middeleware.
	 *
	 * @param \Psr\Http\Message\ServerRequestInterface $request
	 * @return \Psr\Http\Message\ResponseInterface
	 */
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		$jwt = $request->getHeaderLine('Authorization');

		if (empty($jwt)) {
			return new JsonResponse(['error' => 'missing authorization header'], 401);
		}

		try {
			$jwt = substr($jwt, 7);
			$decoded = JWT::decode($jwt, $_ENV['JWT_SECRET'], ['HS256']);
		} catch (\Exception $e) {
			return new JsonResponse(['error' => 'invalid token'], 401);
		}

		$user = User::find($decoded->sub);

		if (empty($user)) {
			return new JsonResponse(['error' => 'invalid user'], 401);
		}

		$this->app->set('user', $user);
		$response = $handler->handle($request);

		return $response;
	}
}
