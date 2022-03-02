<?php
declare(strict_types=1);

use App\Db\Model;
use App\Models\Appointment;

class Booking extends Model {

	public function getAppointment(): Appointment {
		return Appointment::find($this->appointmentId);
	}

	
}