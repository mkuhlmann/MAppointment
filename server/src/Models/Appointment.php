<?php

declare(strict_types=1);

namespace App\Models;

use App\Db\Model;
/**
 * 
 * @package App\Models
 * 
 * @property int $id
 * @property string $name
 * @property bool $requireMailValidation
 * 
 * @property string $mailSender;
 * 
 */
class Appointment extends Model
{
	/**
	 * Generates full URL for this appointment.
	 * @return string Full url to the appointment
	 */
	public function url(): string {
		return $_ENV['BASE_URL'] . '/' . $this->id;
	}
}
