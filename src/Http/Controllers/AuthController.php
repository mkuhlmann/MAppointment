<?php declare(strict_types=1);

namespace App\Http\Controllers;

use Firebase\JWT\JWT;
use App\Config;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\JsonResponse;


class AuthController {
	private Config $config;

	/**
	 * 
	 * @param \App\Config $config
	 */
	public function __construct(Config $config)
	{
		$this->config = $config;	
	}
	 /**
	 * Controller.
	 *
	 * @param \Psr\Http\Message\ServerRequestInterface $request
	 * @return \Psr\Http\Message\ResponseInterface
	 */
	public function login(ServerRequestInterface $request): ResponseInterface
	{
		$jwt = JWT::encode([
			'sub' => 'blabla',
			'exp' =>  time() + 60 * 60 * 24
		], $this->config->get('jwtKey'));

		return new JsonResponse(['jwt' => $jwt]);
	}
}