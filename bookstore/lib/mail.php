<?php

declare(strict_types=1);

function send_app_mail(string $to, string $subject, string $body): bool
{
	$from = (string) config('mail.from');
	$fromName = (string) config('mail.from_name');

	$autoload = PROJECT_ROOT . '/vendor/autoload.php';
	if (is_file($autoload)) {
		require_once $autoload;
		if (class_exists(\PHPMailer\PHPMailer\PHPMailer::class)) {
			try {
				$mail = new \PHPMailer\PHPMailer\PHPMailer(true);
				$mail->isMail();
				$mail->CharSet = 'UTF-8';
				$mail->setFrom($from, $fromName);
				$mail->addAddress($to);
				$mail->Subject = $subject;
				$mail->Body = $body;
				$ok = $mail->send();
				app_log($ok ? 'info' : 'error', 'mail_send_phpmailer', ['to' => $to, 'subject' => $subject]);
				return $ok;
			} catch (Throwable $e) {
				app_log('error', 'mail_send_phpmailer', ['error' => $e->getMessage()]);
			}
		}
	}

	$headers = [
		'From: ' . sprintf('%s <%s>', $fromName, $from),
		'MIME-Version: 1.0',
		'Content-Type: text/plain; charset=UTF-8',
	];

	$ok = @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, implode("\r\n", $headers));
	app_log($ok ? 'info' : 'error', 'mail_send', ['to' => $to, 'subject' => $subject, 'ok' => $ok]);
	return $ok;
}

function send_order_confirmation(string $to, int $orderId, float $amount): bool
{
	$subject = t('order_email_subject', 'Your order confirmation') . " #$orderId";
	$body = sprintf(
		"%s\n\nOrder #%d\nTotal: %s\n\n%s\n",
		t('order_email_intro', 'Thank you for your purchase from Ethiopian Bookstore.'),
		$orderId,
		money_etb($amount),
		t('order_email_outro', 'We will ship your books soon.')
	);
	return send_app_mail($to, $subject, $body);
}
