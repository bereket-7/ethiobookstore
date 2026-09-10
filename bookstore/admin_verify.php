<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';

if (!is_post()) {
	redirect_local('admin.php');
}
require_csrf();

if (login_rate_limited('admin')) {
	flash_set('error', 'Too many login attempts. Try again later.');
	redirect_local('admin.php');
}

$name = trim((string) ($_POST['name'] ?? ''));
$pass = (string) ($_POST['pass'] ?? '');
if ($name === '' || $pass === '') {
	flash_set('error', 'Username and password required');
	redirect_local('admin.php');
}

$conn = db();
$stmt = $conn->prepare('SELECT id, name, pass FROM admin WHERE name = ? LIMIT 1');
$stmt->bind_param('s', $name);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

$valid = false;
if ($row) {
	$hash = (string) $row['pass'];
	if (str_starts_with($hash, '$2y$') || str_starts_with($hash, '$2a$') || str_starts_with($hash, '$argon2')) {
		$valid = password_verify($pass, $hash);
	} else {
		// Legacy SHA-1 migration
		$valid = hash_equals($hash, sha1($pass));
		if ($valid) {
			$newHash = password_hash($pass, PASSWORD_DEFAULT);
			$upd = $conn->prepare('UPDATE admin SET pass = ? WHERE id = ?');
			$id = (int) $row['id'];
			$upd->bind_param('si', $newHash, $id);
			$upd->execute();
			$upd->close();
		}
	}
}

if (!$valid) {
	login_rate_hit('admin');
	flash_set('error', 'Invalid username or password');
	redirect_local('admin.php');
}

login_rate_clear('admin');
session_regenerate_id(true);
$_SESSION['admin_id'] = (int) $row['id'];
$_SESSION['admin_name'] = $row['name'];
flash_set('success', 'Welcome admin');
redirect_local('admin_dashboard.php');
