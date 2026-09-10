<?php

declare(strict_types=1);

/**
 * Load .env from project root (simple KEY=VALUE parser).
 */
function load_env_file(string $path): void
{
	if (!is_file($path)) {
		return;
	}

	$lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	if ($lines === false) {
		return;
	}

	foreach ($lines as $line) {
		$line = trim($line);
		if ($line === '' || str_starts_with($line, '#')) {
			continue;
		}
		if (!str_contains($line, '=')) {
			continue;
		}
		[$key, $value] = explode('=', $line, 2);
		$key = trim($key);
		$value = trim($value, " \t\"'");
		if ($key !== '' && getenv($key) === false) {
			putenv("$key=$value");
			$_ENV[$key] = $value;
		}
	}
}

load_env_file(dirname(__DIR__) . '/.env');

function env(string $key, mixed $default = null): mixed
{
	$value = $_ENV[$key] ?? getenv($key);
	if ($value === false || $value === null || $value === '') {
		return $default;
	}
	return $value;
}

function config(string $key, mixed $default = null): mixed
{
	static $config = null;
	if ($config === null) {
		$config = [
			'app' => [
				'url' => rtrim((string) env('APP_URL', 'http://localhost:8080'), '/'),
				'debug' => filter_var(env('APP_DEBUG', 'true'), FILTER_VALIDATE_BOOLEAN),
				'lang' => (string) env('APP_LANG', 'am'),
				'delivery_fee' => (float) env('DELIVERY_FEE', '25.00'),
				'log_path' => (string) env('LOG_PATH', APP_ROOT . '/storage/logs/app.log'),
			],
			'db' => [
				'host' => (string) env('DB_HOST', '127.0.0.1'),
				'port' => (int) env('DB_PORT', '3306'),
				'name' => (string) env('DB_NAME', 'www_project'),
				'user' => (string) env('DB_USER', 'root'),
				'pass' => (string) env('DB_PASS', ''),
			],
			'mail' => [
				'from' => (string) env('MAIL_FROM', 'noreply@ethiobookstore.local'),
				'from_name' => (string) env('MAIL_FROM_NAME', 'Ethiopian Bookstore'),
			],
			'chapa' => [
				'secret' => (string) env('CHAPA_SECRET', ''),
				'public' => (string) env('CHAPA_PUBLIC', ''),
				'base_url' => rtrim((string) env('CHAPA_BASE_URL', 'https://api.chapa.co/v1'), '/'),
				'webhook_secret' => (string) env('CHAPA_WEBHOOK_SECRET', ''),
			],
		];
	}

	$parts = explode('.', $key);
	$value = $config;
	foreach ($parts as $part) {
		if (!is_array($value) || !array_key_exists($part, $value)) {
			return $default;
		}
		$value = $value[$part];
	}
	return $value;
}
