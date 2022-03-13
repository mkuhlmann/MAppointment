<?php

namespace App\Providers;

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
				$mail->CharSet = 'UTF-8';
				$mail->isSMTP();
				$mail->Host = $_ENV['SMTP_HOST'];
				$mail->SMTPAuth = true;
				$mail->Username = $_ENV['SMTP_USER'];
				$mail->Password = $_ENV['SMTP_PASSWORD'];
				$mail->SMTPSecure = $_ENV['SMTP_SECURE'];
				$mail->Port = $_ENV['SMTP_PORT'];
				return $mail;
			});
	 }

	
}