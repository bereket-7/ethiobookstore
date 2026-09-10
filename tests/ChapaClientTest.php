<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ChapaClientTest extends TestCase
{
	public function testWebhookSignature(): void
	{
		putenv('CHAPA_WEBHOOK_SECRET=testsecret');
		$_ENV['CHAPA_WEBHOOK_SECRET'] = 'testsecret';

		// Rebuild config static by creating client; verifyWebhookSignature reads config()
		// Force reload: config is static — set env before first config() call in process.
		$payload = '{"tx_ref":"eb_abc"}';
		$sig = hash_hmac('sha256', $payload, 'testsecret');

		$client = new ChapaClient('sk_test', 'https://api.chapa.co/v1');
		$this->assertTrue($client->verifyWebhookSignature($payload, $sig));
		$this->assertFalse($client->verifyWebhookSignature($payload, 'nope'));
	}

	public function testNotConfigured(): void
	{
		$client = new ChapaClient('', 'https://api.chapa.co/v1');
		$this->assertFalse($client->isConfigured());
		$result = $client->initialize(['amount' => '10']);
		$this->assertFalse($result['ok']);
	}
}
