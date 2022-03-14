<?php

namespace App\Http\Controllers;

use App\Db\QueryBuilder;
use App\Helper;
use App\Http\ResourceNotFoundJsonResponse;
use App\Models\Booking;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\JsonResponse;
use ParagonIE\EasyDB\EasyDB;

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
		$query = QueryBuilder::open()
			->with('slots.id = ?', $params['id']);
		$query = Booking::getSqlFilterFromRequest($request, $query);
		$appointments = $this->db->run('SELECT bookings.*, slots.start, slots.end FROM bookings LEFT JOIN slots ON bookings.slotId = slots.id WHERE ' . $query, ...$query->values());
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

	public function deleteSlot(ServerRequestInterface $request, array $params): ResponseInterface
	{
		$count = $this->db->row('SELECT COUNT(*) as count FROM bookings WHERE slotId = ?', $params['id']);
		if($count['count'] > 0) {
			return new JsonResponse(['error' => 'Bookings exists for this slot'], 400);
		}
		$this->db->delete('slots', ['id' => $params['id']]);
		return new JsonResponse(['success' => true]);
	}

	public function updateSlot(ServerRequestInterface $request, array $params): ResponseInterface
	{
		$slot = $this->db->row('SELECT * FROM slots WHERE id = ?', $params['id']);
		if (!$slot) {
			return new ResourceNotFoundJsonResponse();
		}

		$body = $request->getParsedBody();

		$this->db->update(
			'slots',
			[
				'start' =>  \dbdate(strtotime($body['start'])),
				'end' => \dbdate(strtotime($body['end'])),
			],
			['id' => $params['id']]
		);

		return $this->getSlot($request, $params);
	}
}
