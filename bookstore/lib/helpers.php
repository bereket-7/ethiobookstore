<?php

declare(strict_types=1);

function db(): mysqli
{
	static $conn = null;
	if ($conn instanceof mysqli) {
		return $conn;
	}

	$host = (string) config('db.host');
	$user = (string) config('db.user');
	$pass = (string) config('db.pass');
	$name = (string) config('db.name');
	$port = (int) config('db.port');

	mysqli_report(MYSQLI_REPORT_OFF);
	$conn = mysqli_init();
	if ($conn === false) {
		app_error('Unable to initialize database connection.');
	}

	if (!@$conn->real_connect($host, $user, $pass, $name, $port)) {
		app_error('Cannot connect to database: ' . mysqli_connect_error());
	}

	$conn->set_charset('utf8mb4');
	return $conn;
}

function e(?string $value): string
{
	return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $path): never
{
	if (!str_starts_with($path, 'http://') && !str_starts_with($path, 'https://')) {
		$path = config('app.url') . '/' . ltrim($path, '/');
		// Prefer relative redirects for local PHP built-in server friendliness
		if (str_starts_with($path, (string) config('app.url'))) {
			$relative = substr($path, strlen((string) config('app.url')));
			header('Location: ' . ($relative !== '' ? $relative : '/'));
			exit;
		}
	}
	header('Location: ' . $path);
	exit;
}

function redirect_local(string $script): never
{
	header('Location: ' . $script);
	exit;
}

function flash_set(string $key, string $message): void
{
	$_SESSION['_flash'][$key] = $message;
}

function flash_get(string $key): ?string
{
	if (!isset($_SESSION['_flash'][$key])) {
		return null;
	}
	$message = $_SESSION['_flash'][$key];
	unset($_SESSION['_flash'][$key]);
	return $message;
}

function t(string $key, ?string $fallback = null): string
{
	$lang = $GLOBALS['LANG'] ?? [];
	return $lang[$key] ?? ($fallback ?? $key);
}

function app_log(string $level, string $message, array $context = []): void
{
	$path = (string) config('app.log_path');
	$dir = dirname($path);
	if (!is_dir($dir)) {
		@mkdir($dir, 0775, true);
	}
	$line = sprintf(
		"[%s] %s: %s %s\n",
		date('c'),
		strtoupper($level),
		$message,
		$context !== [] ? json_encode($context, JSON_UNESCAPED_UNICODE) : ''
	);
	@file_put_contents($path, $line, FILE_APPEND);
}

function app_error(string $message): never
{
	app_log('error', $message);
	if (config('app.debug')) {
		exit($message);
	}
	exit('An unexpected error occurred.');
}

function money_etb(float|string $amount): string
{
	return 'ብር' . number_format((float) $amount, 2);
}

function delivery_fee(): float
{
	return (float) config('app.delivery_fee', 25.0);
}

function request_method(): string
{
	return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
}

function is_post(): bool
{
	return request_method() === 'POST';
}

function pagination(int $total, int $page, int $perPage = 12): array
{
	$totalPages = max(1, (int) ceil($total / $perPage));
	$page = max(1, min($page, $totalPages));
	$offset = ($page - 1) * $perPage;
	return [
		'total' => $total,
		'page' => $page,
		'per_page' => $perPage,
		'total_pages' => $totalPages,
		'offset' => $offset,
	];
}
