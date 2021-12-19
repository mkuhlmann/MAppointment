<?php declare(strict_types=1);

namespace App\Http\Controllers;

use Firebase\JWT\JWT;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\JsonResponse;
use ParagonIE\EasyDB\EasyDB;

class AuthController {
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
	 * Controller.
	 *
	 * @param \Psr\Http\Message\ServerRequestInterface $request
	 * @return \Psr\Http\Message\ResponseInterface
	 */
	public function login(ServerRequestInterface $request): ResponseInterface
	{
		$username = $request->getParsedBody()['username'] ?? null;
		$password = $request->getParsedBody()['password'] ?? null;

		$user = $this->db->row('SELECT * FROM users WHERE username = ? OR email = ?', $username, $username);
		
		if($user && password_verify($password, $user['password'])) {
			$jwt = JWT::encode([
				'sub' => $user['id'],
				'exp' =>  time() + 60 * 60 * 24
			], $_ENV['JWT_SECRET']);
	
			return new JsonResponse(['jwt' => $jwt]);
		}

		return new JsonResponse(['error' => 'invalid credentials']);		
	}
}