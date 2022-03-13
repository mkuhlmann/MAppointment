<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application;
use Firebase\JWT\JWT;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\JsonResponse;
use ParagonIE\EasyDB\EasyDB;

class AuthController {
	private EasyDB $db;
	private Application $app;

	public function __construct(EasyDB $db, Application $app)
	{
		$this->db = $db;
		$this->app = $app;
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
		} else {
			$userCount = $this->db->row('SELECT COUNT(*) as count FROM users');

			if($userCount['count'] == 0) {
				$id = \nanoid();
				$orgId = \nanoid();

				$this->db->insert('organizations', [
					'id' => $orgId,
					'name' => 'My Organization',
					'createdAt' => \dbdate(),
					'updatedAt' => \dbdate()
				]);

				$this->db->insert('users', [
					'id' => $id,
					'organizationId' => $orgId,
					'username' => $username,
					'email' => $username,
					'password' => password_hash($password, PASSWORD_DEFAULT),
					'createdAt' => \dbdate(),
					'updatedAt' => \dbdate()
				]);
				
				$jwt = $jwt = JWT::encode([
					'sub' => $id,
					'exp' =>  time() + 60 * 60 * 24
				], $_ENV['JWT_SECRET']);

				return new JsonResponse(['jwt' => $jwt, 'info' => 'First user created']);
			}
		}

		return new JsonResponse(['error' => 'invalid credentials']);		
	}

	public function isFirstUser(ServerRequestInterface $request): ResponseInterface
	{
		$userCount = $this->db->row('SELECT COUNT(*) as count FROM users');		
		return new JsonResponse($userCount['count'] == 0);
	}

	public function getUser(ServerRequestInterface $request): ResponseInterface
	{
		return new JsonResponse($this->app->get('user'));
	}
}