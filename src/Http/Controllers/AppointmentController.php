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
	public function getBooking(ServerRequestInterface $request, array $params): ResponseInterface
	{
		$id = $params['id'];
		$booking = $this->db->row('SELECT * FROM appointment_slot_bookings as bookings INNER JOIN appointment_slots as slots ON slots.id = bookings.appointmentSlotId INNER JOIN appointments ON appointments.id = slots.appointmentId WHERE bookings.id = ?', $id);

		if (!$booking) {
			return new ResourceNotFoundJsonResponse();
		}
		
		return new JsonResponse($booking);
	}

	public function bookAppointment(ServerRequestInterface $request, array $params): ResponseInterface
	{
		$body = $request->getParsedBody();

		$validator = new Validator();

		$validation = $validator->validate($body, [
			'appointmentId' => 'required',
			'appointmentSlotId' => 'required',
			'firstname' => 'required',
			'lastname' => 'required',
			'email' => 'required|email'
		]);

		if ($validation->fails()) {
			return new JsonResponse(['errors' => $validation->errors()->all(), 'error' => 'Validation failed'], 400);
		}

		$appointmentId = $body['appointmentId'];
		$slotId = $body['appointmentSlotId'];

		$appointment = $this->db->row('SELECT * FROM appointments WHERE id = ?', $appointmentId);
		$slot = $this->db->row('SELECT * FROM appointment_slots WHERE id = ?', $slotId);

		if ($appointment === null || $slot === null || $body['appointmentSlotId'] !== $slot['id']) {
			return new JsonResponse(['error' => 'Appointment/Slot not found'], 404);
		}

		$appointmentSlotBookings = $this->db->run('SELECT * FROM appointment_slot_bookings WHERE appointmentSlotId = ?', $slotId);
		$freeSlots = $slot['slots'] - count($appointmentSlotBookings);

		if ($freeSlots <= 0) {
			return new JsonResponse(['error' => 'No free slots available'], 400);
		}

		$booking = $this->db->row('SELECT * FROM appointment_slot_bookings WHERE email = ?', $body['email']);
		if ($booking !== null) {
			return new JsonResponse(['error' => 'Booking already exists'], 400);
		}

		$bookingId = Helper::nanoid();
		$this->db->insert('appointment_slot_bookings', [
			'id' => $bookingId,
			'secret' => Helper::nanoid(),
			'appointmentSlotId' => $slotId,
			'firstname' => $body['firstname'],
			'lastname' => $body['lastname'],
			'email' => $body['email'],
			'comment' => $body['comment'] ?? null,
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s'),
		]);

		$this->db->update('appointment_slots', [
			'free' => $freeSlots - 1
		], [
			'id' => $slotId
		]);

		return new JsonResponse(['success' => 'Booking successful', 'bookingId' => $bookingId]);
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

		$result = $this->db->run('SELECT DATE_FORMAT(date, \'%Y-%m-%d\') as date, sum(free) as free FROM appointment_slots WHERE free > 0 GROUP BY DATE(date)');
		return new JsonResponse($result);
	}

	public function getAvailableSlots(ServerRequestInterface $request, array $params): ResponseInterface
	{

		if (preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $params['date']) !== 1)	return new ResourceNotFoundJsonResponse();
		$appointment = $this->db->row('SELECT * FROM appointments WHERE id = ?', $params['id']);
		if (!$appointment) return new ResourceNotFoundJsonResponse();

		$result = $this->db->run('SELECT id, date, free, duration FROM appointment_slots WHERE free > 0 AND date >= ? AND date <= ? ORDER BY date ASC', $params['date'] . ' 00:00:00', $params['date'] . ' 23:59:59');
		return new JsonResponse($result);
	}
}
