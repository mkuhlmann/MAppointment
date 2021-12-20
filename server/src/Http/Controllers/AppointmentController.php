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

class AppointmentController
{
	private EasyDB $db;

	public function __construct(EasyDB $db)
	{
		$this->db = $db;
	}

	/** Admin Routes */
	public function getAppointments(ServerRequestInterface $request, array $params): ResponseInterface
	{
		$appointments = $this->db->run('SELECT * FROM appointments');
		return new JsonResponse($appointments);
	}

	/** Public Routes */
	public function getSlot(ServerRequestInterface $request, array $params): ResponseInterface
	{
		$slot = $this->db->row('SELECT * FROM slots WHERE id = ?', $params['id']);
		if (!$slot) {
			return new ResourceNotFoundJsonResponse();
		}
		return new JsonResponse($slot);
	}

	public function getAppointment(ServerRequestInterface $request, array $params): ResponseInterface
	{
		$appointment = $this->db->row('SELECT * FROM appointments WHERE id = ?', $params['id']);
		if (!$appointment) return new ResourceNotFoundJsonResponse();

		return new JsonResponse($appointment);
	}

	public function getAvailableDates(ServerRequestInterface $request, array $params): ResponseInterface
	{
		$appointment = $this->db->row('SELECT * FROM appointments WHERE id = ?', $params['id']);
		if (!$appointment) return new ResourceNotFoundJsonResponse();

		$result = $this->db->run('SELECT DATE_FORMAT(date, \'%Y-%m-%d\') as date, sum(free) as free FROM slots WHERE free > 0 GROUP BY DATE(date)');
		return new JsonResponse($result);
	}

	public function getAvailableSlots(ServerRequestInterface $request, array $params): ResponseInterface
	{

		if (preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $params['date']) !== 1)	return new ResourceNotFoundJsonResponse();
		$appointment = $this->db->row('SELECT * FROM appointments WHERE id = ?', $params['id']);
		if (!$appointment) return new ResourceNotFoundJsonResponse();

		$result = $this->db->run('SELECT id, date, free, duration FROM slots WHERE free > 0 AND date >= ? AND date <= ? ORDER BY date ASC', $params['date'] . ' 00:00:00', $params['date'] . ' 23:59:59');
		return new JsonResponse($result);
	}
}
