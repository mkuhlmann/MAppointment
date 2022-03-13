<?php

namespace App\Models;

use App\Db\Model;

/**
 * @package App\Models
 * 
 * @property int $id
 * @property Appointment $appointment
 * 
 * @property string $start
 * @property string $end
 * 
 * @property string $createdAt
 * @property string $updatedAt
 */
class Slot extends Model {
	public function appointment() {
		return $this->belongsTo(Appointment::class);
	}
}