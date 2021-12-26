<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helper;
use App\Http\ResourceNotFoundJsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\JsonResponse;
use ParagonIE\EasyDB\EasyDB;
use Rakit\Validation\Validator;

class SlotController
{
	private EasyDB $db;

	public function __construct(EasyDB $db)
	{
		$this->db = $db;
	}

	/** Admin Routes */
	public function getBookings(ServerRequestInterface $request, array $params): ResponseInterface
	{
		$appointments = $this->db->run('SELECT * FROM bookings WHERE slotId = ?', $params['id']);
		return new JsonResponse($appointments);
	}

	public function getSlot(ServerRequestInterface $request, array $params): ResponseInterface
	{
		$slot = $this->db->row('SELECT * FROM slots WHERE id = ?', $params['id']);
		if (!$slot) {
			return new ResourceNotFoundJsonResponse();
		}
		return new JsonResponse($slot);
	}

}
