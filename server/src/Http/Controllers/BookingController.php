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

class BookingController
{
	private EasyDB $db;

	public function __construct(EasyDB $db)
	{
		$this->db = $db;
	}

	public function getBooking(ServerRequestInterface $request, array $params): ResponseInterface
	{
		$booking = $this->db->row('SELECT * FROM bookings WHERE id = ?', $params['id']);
		if (!$booking) {
			return new ResourceNotFoundJsonResponse();
		}
		
		return new JsonResponse($booking);
	}

	public function getLatestBookings(ServerRequestInterface $request): ResponseInterface	{
		$bookings = $this->db->run('SELECT bookings.*, appointments.name, slots.appointmentId FROM bookings LEFT JOIN slots ON slots.id = bookings.slotId LEFT JOIN appointments ON appointments.id = slots.appointmentId ORDER BY bookings.createdAt DESC LIMIT 20');
		return new JsonResponse($bookings);
	}

	public function getBookingStats(ServerRequestInterface $request): ResponseInterface
	{
		$stats = $this->db->run('SELECT count(*) as y, date(createdAt) as x FROM bookings WHERE createdAt > DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY x');
		return new JsonResponse($stats);
	}

	public function bookAppointment(ServerRequestInterface $request, array $params): ResponseInterface
	{
		$body = $request->getParsedBody();

		$validator = new Validator();

		$validation = $validator->validate($body, [
			'appointmentId' => 'required',
			'slotId' => 'required',
			'firstName' => 'required',
			'lastName' => 'required',
			'email' => 'required|email'
		]);

		if ($validation->fails()) {
			return new JsonResponse(['errors' => $validation->errors()->all(), 'error' => 'Validation failed'], 400);
		}

		$appointmentId = $body['appointmentId'];
		$slotId = $body['slotId'];

		$appointment = $this->db->row('SELECT * FROM appointments WHERE id = ?', $appointmentId);
		$slot = $this->db->row('SELECT * FROM slots WHERE id = ?', $slotId);

		if ($appointment === null || $slot === null || $body['appointmentId'] !== $appointment['id']) {
			return new JsonResponse(['error' => 'Appointment/Slot not found'], 404);
		}

		$appointmentSlotBookings = $this->db->run('SELECT * FROM bookings WHERE slotId = ?', $slotId);
		$freeSlots = $slot['slots'] - count($appointmentSlotBookings);

		if ($freeSlots <= 0) {
			return new JsonResponse(['error' => 'No free slots available'], 400);
		}

		$booking = $this->db->row('SELECT * FROM bookings WHERE email = ?', $body['email']);
		if ($booking !== null) {
			return new JsonResponse(['error' => 'Buchung existiert bereits.'], 400);
		}

		$bookingId = Helper::nanoid();
		$this->db->insert('bookings', [
			'id' => $bookingId,
			'secret' => Helper::nanoid(),
			'slotId' => $slotId,
			'firstName' => $body['firstName'],
			'lastName' => $body['lastName'],
			'email' => $body['email'],
			'comment' => $body['comment'] ?? null,
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s'),
		]);

		$this->db->update('slots', [
			'free' => $freeSlots - 1
		], [
			'id' => $slotId
		]);

		return new JsonResponse(['success' => 'Booking successful', 'bookingId' => $bookingId]);
	}
}