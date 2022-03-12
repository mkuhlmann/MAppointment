<?php

namespace App\Models;

use App\Db\Model;
use App\Models\Appointment;
use App\Models\Slot;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Booking Model
 * 
 * @package App\Models
 * 
 * @property string $id
 * @property Appointment $appointment
 * @property Slot $slot
 * 
 * @property string $firstName
 * @property string $lastName
 * @property string $email
 * @property string $phone
 * @property string $comment
 * 
 * @property string $createdAt
 * @property string $updatedAt
 */
class Booking extends Model
{
	protected array $guarded = ['id'];

	public function appointment(): Appointment
	{
		return $this->slot->appointment;
	}

	public function slot(): Slot
	{
		return $this->belongsTo(Slot::class);
	}

	public function sendMail()
	{
		/** @var PHPMailer */
		$mailer = app()->get(PHPMailer::class);

		$mailer->setFrom($this->appointment->mailSender, $this->appointment->mailSenderName);
		$mailer->addAddress($this->email, $this->firstName . ' ' . $this->lastName);

		if ($this->appointment->requireMailValidation && !$this->mailConfirmedAt) {
			$mailer->Subject = $this->replacePlaceholders($this->appointment->mailSubjectValidate);
			$mailer->Body = $this->replacePlaceholders($this->appointment->mailBodyValidate);
		} else {
			$mailer->Subject = $this->replacePlaceholders($this->appointment->mailSubject);
			$mailer->Body = $this->replacePlaceholders($this->appointment->mailBody);
		}

		if ($mailer->send()) {
			$this->mailSentAt = \dbdate();
			$this->save();
		} else {
			throw new \Exception($mailer->ErrorInfo);
		}
	}

	public function confirmUrl() {
		return $this->url() . '?c=' . $this->secret;
	}

	public function url() {
		return $_ENV['BASE_URL'] . '/booking/' . $this->id;
	}

	public function mailDetails($isHtml = false) {
		$text = $isHtml ? '<h2>Buchungsübersicht</h2>' : 'Buchungsübersicht';
		$text .= $isHtml ? '<table>' : "\n";

		$rows = [
			'Name' => $this->firstName . ' ' . $this->lastName,
			'E-Mail' => $this->email,
			'Kommentar' => $this->comment,
			'Datum' => date('d.m.Y', strtotime($this->slot->date)),
			'Uhrzeit' => $this->slot->time,
			'Buchungsnummer' => $this->id,
			'Buchung verwalten' => $this->url(),
		];

		foreach ($rows as $label => $value) {
			if($isHtml) {
				$text .= '<tr><td>' . $label . '</td><td>' . $value . '</td></tr>';
			} else {
				$text .= str_pad($label . ':', 20) . $value . "\n";
			}
		}

		$text .= $isHtml ? '</table>' : '';
		return $text;
		
	}

	public function replacePlaceholders($text, $isHtml = false): string {
		$placeholders = [
			'{firstName}' => $this->firstName,
			'{lastName}' => $this->lastName,
			'{email}' => $this->email,
			'{phone}' => $this->phone,
			'{comment}' => $this->comment,
			'{appointmentName}' => $this->appointment->name,
			'{appointmentDate}' => $this->appointment->date,
			'{appointmentTime}' => $this->appointment->time,
			'{appointmentLocation}' => $this->appointment->location,
			'{confirmUrl}' => $this->confirmUrl(),
			'{url}' => $this->url(),
			'{bookingDetail}' => $this->mailDetails($isHtml),
		];

		return str_replace(array_keys($placeholders), array_values($placeholders), $text);
	}
}
