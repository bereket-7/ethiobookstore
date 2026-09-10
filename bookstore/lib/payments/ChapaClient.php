<?php

declare(strict_types=1);

final class ChapaClient
{
	public function __construct(
		private readonly string $secret,
		private readonly string $baseUrl,
	) {
	}

	public static function fromConfig(): self
	{
		return new self(
			(string) config('chapa.secret'),
			(string) config('chapa.base_url'),
		);
	}

	public function isConfigured(): bool
	{
		return $this->secret !== '';
	}

	/**
	 * @param array<string, mixed> $payload
	 * @return array{ok:bool, checkout_url?:string, tx_ref?:string, error?:string, raw?:array}
	 */
	public function initialize(array $payload): array
	{
		if (!$this->isConfigured()) {
			return ['ok' => false, 'error' => 'Chapa is not configured. Set CHAPA_SECRET in .env'];
		}

		$response = $this->request('POST', '/transaction/initialize', $payload);
		if (!($response['status'] ?? false) && ($response['message'] ?? '') !== 'success' && ($response['data']['checkout_url'] ?? null) === null) {
			$msg = is_string($response['message'] ?? null) ? $response['message'] : 'initialize_failed';
			return ['ok' => false, 'error' => $msg, 'raw' => $response];
		}

		$checkout = $response['data']['checkout_url'] ?? null;
		$txRef = $response['data']['tx_ref'] ?? ($payload['tx_ref'] ?? null);
		if (!$checkout) {
			return ['ok' => false, 'error' => 'missing_checkout_url', 'raw' => $response];
		}

		return [
			'ok' => true,
			'checkout_url' => $checkout,
			'tx_ref' => (string) $txRef,
			'raw' => $response,
		];
	}

	/**
	 * @return array{ok:bool, data?:array, error?:string}
	 */
	public function verify(string $txRef): array
	{
		$response = $this->request('GET', '/transaction/verify/' . rawurlencode($txRef));
		$status = $response['data']['status'] ?? null;
		if ($status === 'success' || $status === 'successful') {
			return ['ok' => true, 'data' => $response['data'] ?? []];
		}
		return ['ok' => false, 'error' => 'not_paid', 'data' => $response];
	}

	public function verifyWebhookSignature(string $payload, ?string $signature): bool
	{
		$secret = (string) env('CHAPA_WEBHOOK_SECRET', config('chapa.webhook_secret', ''));
		if ($secret === '' || $signature === null || $signature === '') {
			return false;
		}
		$expected = hash_hmac('sha256', $payload, $secret);
		return hash_equals($expected, $signature);
	}

	/**
	 * @param array<string, mixed>|null $body
	 * @return array<string, mixed>
	 */
	private function request(string $method, string $path, ?array $body = null): array
	{
		$url = $this->baseUrl . $path;
		$ch = curl_init($url);
		$headers = [
			'Authorization: Bearer ' . $this->secret,
			'Content-Type: application/json',
		];
		curl_setopt_array($ch, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CUSTOMREQUEST => $method,
			CURLOPT_HTTPHEADER => $headers,
			CURLOPT_TIMEOUT => 30,
		]);
		if ($body !== null) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_UNESCAPED_UNICODE));
		}
		$raw = curl_exec($ch);
		$errno = curl_errno($ch);
		$error = curl_error($ch);
		curl_close($ch);

		if ($errno) {
			app_log('error', 'chapa_curl', ['error' => $error]);
			return ['message' => $error];
		}

		$decoded = json_decode((string) $raw, true);
		return is_array($decoded) ? $decoded : ['message' => 'invalid_json', 'raw' => $raw];
	}
}
