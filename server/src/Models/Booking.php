<?php

namespace App\Models;

use App\Db\Model;
use App\Models\Appointment;
use App\Models\Slot;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * 
 * @property Appointment $appointment
 * @property Slot $slot
 */
class Booking extends Model {

	public function appointment(): Appointment {
		return $this->slot->appointment;
	}

	public function slot(): Slot {
		return $this->belongsTo(Slot::class);
	}

	public function sendMail($resend = false) {
		/** @var PHPMailer */
		$mailer = app()->get(PHPMailer::class);


		$mailer->setFrom($this->appointment->mailSender, $this->appointment->mailSenderName);
		$mailer->addAddress($this->email, $this->firstName . ' ' . $this->lastName);
		
		if($this->appointment->requireMailValidation && $this->secret) {
			$mailer->addReplyTo($this->appointment->mailSender, $this->appointment->mailSenderName);
			$mailer->Subject = 'Buchung bestätigen - ' . $this->appointment->name;
			$mailer->Body = 'Hallo ' . $this->firstName . ' ' . $this->lastName . ',<br><br>
							Bitte bestätige deine Buchung mit folgendem Link:<br><br>
							<a href="' . $this->appointment->url . '/confirm/' . $this->id . '/' . $this->secret . '">' . $this->appointment->url . '/confirm/' . $this->id . '/' . $this->secret . '</a><br><br>
							Vielen Dank für deine Buchung.<br><br>
							Dein Buchungs-Team';
		} else {
			$mailer->Subject = 'Buchung bestätigt - ' . $this->appointment->name;
			$mailer->Body = 'Hallo ' . $this->firstName . ' ' . $this->lastName . ',<br><br>
							Deine Buchung wurde bestätigt.<br><br>
							Vielen Dank für deine Buchung.<br><br>
							Dein Buchungs-Team';
		}

		if($mailer->send()) {
			$this->mailSent = true;
			$this->save();
		} else {
			throw new \Exception('Mail could not be sent.');
		}
	}


	
}