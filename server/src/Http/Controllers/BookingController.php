<?php

namespace App\Http\Controllers;

use App\Db\QueryBuilder;
use App\Http\ResourceNotFoundJsonResponse;
use App\Models\Appointment;
use App\Models\Booking;
use Exception;
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

	public function getLatestBookings(ServerRequestInterface $request): ResponseInterface
	{
		$bookings = $this->db->run('SELECT bookings.*, appointments.name, slots.appointmentId FROM bookings LEFT JOIN slots ON slots.id = bookings.slotId LEFT JOIN appointments ON appointments.id = slots.appointmentId ORDER BY bookings.createdAt DESC LIMIT 20');
		return new JsonResponse($bookings);
	}

	public function getBookingStats(ServerRequestInterface $request): ResponseInterface
	{
		$stats = $this->db->run('SELECT count(*) as y, date(createdAt) as x FROM bookings WHERE createdAt > DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY x');
		return new JsonResponse($stats);
	}

	public function updateBooking(ServerRequestInterface $request, array $params): ResponseInterface
	{
		$booking = Booking::find($params['id']);
		if (!$booking) {
			return new ResourceNotFoundJsonResponse();
		}

		$body = $request->getParsedBody();

		$booking->fill($body, true);
		$booking->save();

		return new JsonResponse($booking);
	}

	public function bookAppointment(ServerRequestInterface $request, array $params): ResponseInterface
	{
		$body = $request->getParsedBody();

		if(!isset($body['appointmentId']) || !isset($body['slotId'])) {
			return new JsonResponse(['error' => 'Missing arguments'], 400);
		}

		$appointmentId = $body['appointmentId'];
		$slotId = $body['slotId'];

		$appointment = Appointment::find($appointmentId);
		$slot = $this->db->row('SELECT * FROM slots WHERE id = ?', $slotId);

		if ($appointment === null || $slot === null || $body['appointmentId'] !== $appointment['id']) {
			return new JsonResponse(['error' => 'Veranstaltung nicht gefunden.'], 404);
		}

		$validator = new Validator();

		$rules = [
			'appointmentId' => 'required',
			'slotId' => 'required',
			'firstName' => 'required',
			'lastName' => 'required',
			'email' => 'required|email',
			'timezone' => 'required',
			'language' => 'required'
		];

		if($appointment->requirePhoneNumber) {
			$rules['phone'] = 'required';
		}
		
		$validation = $validator->validate($body, $rules);

		if ($validation->fails()) {
			return new JsonResponse(['errors' => $validation->errors()->all(), 'error' => 'Validation failed'], 400);
		}

		$appointmentSlotBookings = $this->db->run('SELECT * FROM bookings WHERE slotId = ?', $slotId);
		$freeSlots = $slot['slots'] - count($appointmentSlotBookings);

		if ($freeSlots <= 0) {
			return new JsonResponse(['error' => 'Termin bereits belegt.'], 400);
		}

		$booking = $this->db->row('SELECT * FROM bookings WHERE email = ?', $body['email']);
		if ($booking !== null) {
			return new JsonResponse(['error' => 'Buchung existiert bereits.'], 400);
		}

		try {
			$booking = Booking::create([
				'secret' => \nanoid(),
				'slotId' => $slotId,
				'firstName' => $body['firstName'],
				'lastName' => $body['lastName'],
				'email' => $body['email'],
				'phone' => $body['phone'] ?? null,
				'comment' => $body['comment'] ?? null,
				'timezone' => $body['timezone'],
				'language' => $body['language'],
				'confirmedAt' => ($appointment->requireMailValidation ? null : \dbdate()),
			]);
		} catch (Exception $ex) {
			return new JsonResponse(['error' => 'Buchung konnte nicht erstellt werden.'], 500);
		}

		$this->db->update('slots', [
			'free' => $freeSlots - 1
		], [
			'id' => $slotId
		]);

		$mailSuccess = true;

		try {			
			$booking->sendMail();
		} catch(Exception $ex) {
			$mailSuccess = false;
		}

		return new JsonResponse(['success' => 'Booking successful', 'bookingId' => $booking->id, 'mailSuccess' => $mailSuccess]);
	}

	public function confirmBooking(ServerRequestInterface $request, array $params): ResponseInterface
	{
		$body = $request->getParsedBody();

		$booking = Booking::findOrFail($params['id']);
		if (!$booking) {
			return new ResourceNotFoundJsonResponse();
		}

		if ($booking['secret'] !== $body['secret']) {
			return new JsonResponse(['error' => 'Invalid secret'], 400);
		}


		$booking->confirmedAt = \dbdate();
		$booking->sendMail();
		$booking->save();

		return new JsonResponse(['success' => 'Booking confirmed']);
	}


	public function cancelBooking(ServerRequestInterface $request, array $params): ResponseInterface
	{
		$booking = Booking::find($params['id']);
		if (!$booking) {
			return new ResourceNotFoundJsonResponse();
		}

		$body = $request->getParsedBody();

		if ($booking->isCancellable() && $booking->cancel($body['sendCancellationMail'] ?? true)) {
			return new JsonResponse(['success' => 'Buchung storniert.']);
		}

		return new JsonResponse(['error' => 'Buchung kann nicht mehr storniert werden.'], 500);
	}

	public function sendMail(ServerRequestInterface $request, array $params): ResponseInterface
	{
		$booking = Booking::find($params['id']);
		if (!$booking) {
			return new ResourceNotFoundJsonResponse();
		}

		try {
			$booking->sendMail();
			return new JsonResponse(['success' => 'Mail sent']);
		} catch (\Exception $e) {
			return new JsonResponse(['error' => $e->getMessage()], 500);
		}
	}
}
