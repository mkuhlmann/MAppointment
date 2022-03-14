<?php

namespace App\Models;

use App\Db\Model;
use App\Db\QueryBuilder;
use App\Models\Appointment;
use App\Models\Slot;
use PHPMailer\PHPMailer\PHPMailer;
use Psr\Http\Message\ServerRequestInterface;

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
 * @property string $timezone
 * @property string $language
 * 
 * @property bool $cancellationEnabled
 * @property int $cancellationDeadline
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

	public function isCancellable(): bool
	{
		return $this->appointment->cancellationEnabled && (strtotime($this->slot->start) - time()) > $this->appointment->cancellationDeadline * 60 || app()->get('user') != null;
	}

	public function sendMail()
	{
		/** @var PHPMailer */
		$mailer = app()->get(PHPMailer::class);

		$mailer->setFrom($this->appointment->mailSender, $this->appointment->mailSenderName);
		$mailer->addAddress($this->email, $this->firstName . ' ' . $this->lastName);

		if ($this->appointment->requireMailValidation && !$this->confirmedAt) {
			$mailer->Subject = $this->replacePlaceholders($this->appointment->mailSubjectValidate);
			$mailer->Body = $this->replacePlaceholders($this->appointment->mailBodyValidate);
		} else {
			$mailer->Subject = $this->replacePlaceholders($this->appointment->mailSubjectConfirmation);
			$mailer->Body = $this->replacePlaceholders($this->appointment->mailBodyConfirmation);
		}

		if ($mailer->send()) {
			$this->mailSentAt = \dbdate();
			$this->save();
		} else {
			throw new \Exception($mailer->ErrorInfo);
		}
	}

	public function sendCancellationMail()
	{
		/** @var PHPMailer */
		$mailer = app()->get(PHPMailer::class);

		$mailer->setFrom($this->appointment->mailSender, $this->appointment->mailSenderName);
		$mailer->addAddress($this->email, $this->firstName . ' ' . $this->lastName);

		$mailer->Subject = $this->replacePlaceholders($this->appointment->mailSubjectCancellation);
		$mailer->Body = $this->replacePlaceholders($this->appointment->mailBodyCancellation);

		if ($mailer->send()) {
			$this->mailSentAt = \dbdate();
			$this->save();
		} else {
			throw new \Exception($mailer->ErrorInfo);
		}
	}

	public function cancel($sendMail = true): bool
	{
		try {
			$this->delete();

			if ($sendMail) {
				$this->sendCancellationMail();
			}
			return true;
		} catch (\Exception $e) {
			return false;
		}
	}

	public function confirmUrl()
	{
		return $this->url() . '?c=' . $this->secret;
	}

	public function url()
	{
		return $_ENV['BASE_URL'] . '/booking/' . $this->id;
	}

	public function mailDetails($isHtml = false)
	{
		$text = $isHtml ? '<h2>Buchungsübersicht</h2>' : 'Buchungsübersicht';
		$text .= $isHtml ? '<table>' : "\n";

		$rows = [
			'Name' => $this->firstName . ' ' . $this->lastName,
			'E-Mail' => $this->email,
			'Kommentar' => $this->comment,
			'Datum' => tzdate('d.m.Y', $this->slot->start, $this->timezone),
			'Uhrzeit' => tzdate('H:i', $this->slot->start, $this->timezone),
			'Buchungsnummer' => $this->id,
			'Buchung verwalten' => $this->url(),
		];

		foreach ($rows as $label => $value) {
			if ($isHtml) {
				$text .= '<tr><td>' . $label . '</td><td>' . $value . '</td></tr>';
			} else {
				$text .= str_pad($label . ':', 20) . $value . "\n";
			}
		}

		$text .= $isHtml ? '</table>' : '';
		return $text;
	}

	public function replacePlaceholders($text, $isHtml = false): string
	{
		$placeholders = [
			'{firstName}' => $this->firstName,
			'{lastName}' => $this->lastName,
			'{email}' => $this->email,
			'{phone}' => $this->phone,
			'{comment}' => $this->comment,
			'{appointmentName}' => $this->appointment->name,
			'{appointmentDate}' => tzdate('d.m.Y', $this->slot->start, $this->timezone),
			'{appointmentTime}' => tzdate('H:i', $this->slot->start, $this->timezone),
			'{appointmentLocation}' => $this->appointment->location,
			'{confirmUrl}' => $this->confirmUrl(),
			'{url}' => $this->url(),
			'{bookingDetail}' => $this->mailDetails($isHtml),
			'{mailSenderName}' => $this->appointment->mailSenderName
		];

		return str_replace(array_keys($placeholders), array_values($placeholders), $text);
	}

	public function delete()
	{
		$this->slot->free++;
		$this->slot->save();

		parent::delete();
	}
	
	public static function getSqlFilterFromRequest(ServerRequestInterface $request, QueryBuilder $query): QueryBuilder
	{
		$q = $request->getQueryParams();

		if(isset($q['q']) && !empty(trim($q['q']))) {
			$q['query'] = '%' . db()->escapeLikeValue(trim($q['q'])) . '%';
			$query = $query
				->andGroup()
					->with('firstName LIKE ?', $q['q'])
					->orWith('lastName LIKE ?', $q['q'])
					->orWith('email LIKE ?', $q['q'])
					->endGroup();
		}

		if(isset($q['onlyUpcoming']) && $q['onlyUpcoming'] == '1') {
			$query = $query->andWith('slots.start > ?', \dbdate());
		}

		return $query;
	}
}
