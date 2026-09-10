<?php

declare(strict_types=1);

function csrf_token(): string
{
	if (empty($_SESSION['_csrf'])) {
		$_SESSION['_csrf'] = bin2hex(random_bytes(32));
	}
	return $_SESSION['_csrf'];
}

function csrf_field(): string
{
	return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_verify(?string $token = null): bool
{
	$token ??= $_POST['_csrf'] ?? '';
	$session = $_SESSION['_csrf'] ?? '';
	return is_string($token) && is_string($session) && $session !== '' && hash_equals($session, $token);
}

function require_csrf(): void
{
	if (!csrf_verify()) {
		flash_set('error', t('csrf_invalid', 'Invalid security token. Please try again.'));
		redirect_local($_SERVER['HTTP_REFERER'] ?? 'index.php');
	}
}
