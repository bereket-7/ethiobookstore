<?php

declare(strict_types=1);

function admin_logged_in(): bool
{
	return !empty($_SESSION['admin_id']);
}

function require_admin(): void
{
	if (!admin_logged_in()) {
		flash_set('error', t('admin_login_required', 'Please log in as admin.'));
		redirect_local('admin.php');
	}
}

function customer_logged_in(): bool
{
	return !empty($_SESSION['user_id']);
}

function current_user_id(): ?int
{
	return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

function require_customer(): void
{
	if (!customer_logged_in()) {
		flash_set('error', t('login_required', 'Please log in to continue.'));
		redirect_local('login.php');
	}
}

function login_rate_limited(string $bucket, int $maxAttempts = 5, int $windowSeconds = 900): bool
{
	$key = 'login_attempts_' . $bucket;
	$now = time();
	$attempts = $_SESSION[$key] ?? [];
	$attempts = array_values(array_filter($attempts, static fn ($ts) => ($now - (int) $ts) < $windowSeconds));
	$_SESSION[$key] = $attempts;
	return count($attempts) >= $maxAttempts;
}

function login_rate_hit(string $bucket): void
{
	$key = 'login_attempts_' . $bucket;
	$_SESSION[$key][] = time();
}

function login_rate_clear(string $bucket): void
{
	unset($_SESSION['login_attempts_' . $bucket]);
}
