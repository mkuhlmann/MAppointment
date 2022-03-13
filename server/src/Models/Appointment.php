<?php
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
 * @property string $mailSenderName;
 * 
 * @property string $mailSubjectValidate
 * @property string $mailBodyValidate
 * 
 * @property string $mailSubjectConfirmation
 * @property string $mailBodyConfirmation
 * 
 * @property string $mailSubjectCancellation
 * @property string $mailBodyCancellation 
 */
class Appointment extends Model
{
	protected array $guarded = [];

	/**
	 * Generates full URL for this appointment.
	 * @return string Full url to the appointment
	 */
	public function url(): string {
		return $_ENV['BASE_URL'] . '/' . $this->id;
	}

	
	public function fillDefaultMailTemplate() {
		$this->mailSubjectValidate = 'Buchung bestätigen - {appointmentName}';
		$this->mailBodyValidate = file_get_contents(app()->path('/resources/mail/bookingValidate.txt'));

		$this->mailSubjectConfirmation = 'Buchung bestätigt - {appointmentName}';
		$this->mailBodyConfirmation = file_get_contents(app()->path('/resources/mail/bookingConfirmation.txt'));

		$this->mailSubjectCancellation = 'Buchung storniert - {appointmentName}';
		$this->mailBodyCancellation = file_get_contents(app()->path('/resources/mail/bookingCancellation.txt'));
	}
}
