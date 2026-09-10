<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';

if (customer_logged_in()) {
	redirect_local('my_orders.php');
}

if (is_post()) {
	require_csrf();
	$name = trim((string) ($_POST['name'] ?? ''));
	$email = trim((string) ($_POST['email'] ?? ''));
	$password = (string) ($_POST['password'] ?? '');
	if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
		flash_set('error', 'Name, valid email, and password (8+ chars) required.');
		redirect_local('register.php');
	}
	$hash = password_hash($password, PASSWORD_DEFAULT);
	$conn = db();
	$stmt = $conn->prepare('INSERT INTO users (email, password, name) VALUES (?, ?, ?)');
	$stmt->bind_param('sss', $email, $hash, $name);
	if (!$stmt->execute()) {
		flash_set('error', 'Email already registered.');
		redirect_local('register.php');
	}
	$userId = (int) $stmt->insert_id;
	$stmt->close();
	session_regenerate_id(true);
	$_SESSION['user_id'] = $userId;
	$_SESSION['user_name'] = $name;
	$_SESSION['user_email'] = $email;
	flash_set('success', 'Account created');
	redirect_local('index.php');
}

$title = t('nav_register');
require_once __DIR__ . '/template/header.php';
?>
<form method="post" action="register.php" class="form-horizontal" style="max-width:480px;margin:auto;">
	<?php echo csrf_field(); ?>
	<div class="form-group">
		<label>Name</label>
		<input type="text" name="name" class="form-control" required>
	</div>
	<div class="form-group">
		<label>Email</label>
		<input type="email" name="email" class="form-control" required>
	</div>
	<div class="form-group">
		<label>Password</label>
		<input type="password" name="password" class="form-control" minlength="8" required>
	</div>
	<button class="btn btn-primary" type="submit"><?php echo e(t('nav_register')); ?></button>
</form>
<?php require_once __DIR__ . '/template/footer.php'; ?>
