<?php declare(strict_types=1);

namespace App\Http\Controllers;

use Firebase\JWT\JWT;
use App\Config;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\JsonResponse;
use ParagonIE\EasyDB\EasyDB;

class AuthController {
	private Config $config;
	private EasyDB $db;

	/**
	 * 
	 * @param \App\Config $config
	 */
	public function __construct(Config $config, EasyDB $db)
	{
		$this->config = $config;
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

		$user = $this->db->row('SELECT * FROM users WHERE username = ?', $username);
		
		if($user && password_verify($password, $user['password'])) {
			$jwt = JWT::encode([
				'sub' => $user['id'],
				'exp' =>  time() + 60 * 60 * 24
			], $this->config->get('jwtKey'));
	
			return new JsonResponse(['jwt' => $jwt]);
		}

		return new JsonResponse(['error' => 'invalid credentials']);		
	}
}