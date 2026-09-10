<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';

if (customer_logged_in()) {
	redirect_local('my_orders.php');
}

if (is_post()) {
	require_csrf();
	if (login_rate_limited('customer')) {
		flash_set('error', 'Too many login attempts. Try again later.');
		redirect_local('login.php');
	}
	$email = trim((string) ($_POST['email'] ?? ''));
	$password = (string) ($_POST['password'] ?? '');
	$conn = db();
	$stmt = $conn->prepare('SELECT id, name, email, password FROM users WHERE email = ? LIMIT 1');
	$stmt->bind_param('s', $email);
	$stmt->execute();
	$user = $stmt->get_result()->fetch_assoc();
	$stmt->close();

	if (!$user || !password_verify($password, $user['password'])) {
		login_rate_hit('customer');
		flash_set('error', 'Invalid email or password');
		redirect_local('login.php');
	}

	login_rate_clear('customer');
	session_regenerate_id(true);
	$_SESSION['user_id'] = (int) $user['id'];
	$_SESSION['user_name'] = $user['name'];
	$_SESSION['user_email'] = $user['email'];
	flash_set('success', 'Welcome, ' . $user['name']);
	redirect_local('index.php');
}

$title = t('nav_login');
require_once __DIR__ . '/template/header.php';
?>
<form method="post" action="login.php" class="form-horizontal" style="max-width:480px;margin:auto;">
	<?php echo csrf_field(); ?>
	<div class="form-group">
		<label>Email</label>
		<input type="email" name="email" class="form-control" required>
	</div>
	<div class="form-group">
		<label>Password</label>
		<input type="password" name="password" class="form-control" required>
	</div>
	<button class="btn btn-primary" type="submit"><?php echo e(t('nav_login')); ?></button>
	<a href="register.php"><?php echo e(t('nav_register')); ?></a>
</form>
<?php require_once __DIR__ . '/template/footer.php'; ?>
