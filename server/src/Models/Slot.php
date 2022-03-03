<?php

namespace App\Models;

use App\Db\Model;

/**
 * 
 * @package App\Models
 * @property Appointment $appointment
 */
class Slot extends Model {
	public function appointment() {
		return $this->belongsTo(Appointment::class);
	}
}