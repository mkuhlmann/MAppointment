<?php

namespace App\ServiceProvider;

use League\Container\ServiceProvider\AbstractServiceProvider;
use PHPMailer\PHPMailer\PHPMailer;

class MailServiceProvider extends AbstractServiceProvider {

    public function provides(string $id): bool {		
		return $id == PHPMailer::class;
	 }

    public function register(): void {		
		$container = $this->getContainer();

		$container
			->add(PHPMailer::class, function() {
				$mail = new PHPMailer();
				$mail->isSMTP();
				$mail->Host = getenv('SMTP_HOST');
				$mail->SMTPAuth = true;
				$mail->Username = getenv('SMTP_USER');
				$mail->Password = getenv('SMTP_PASSWORD');
				$mail->SMTPSecure = 'tls';
				$mail->Port = 587;
				return $mail;
			});
	 }

	
}