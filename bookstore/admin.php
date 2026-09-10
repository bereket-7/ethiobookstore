<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';

if (admin_logged_in()) {
	redirect_local('admin_dashboard.php');
}

$title = 'Administration';
require_once __DIR__ . '/template/header.php';
?>
<form class="form-horizontal" method="post" action="admin_verify.php" style="max-width:480px;margin:auto;">
	<?php echo csrf_field(); ?>
	<div class="form-group">
		<label class="control-label">Username</label>
		<input type="text" name="name" class="form-control" required>
	</div>
	<div class="form-group">
		<label class="control-label">Password</label>
		<input type="password" name="pass" class="form-control" required>
	</div>
	<input type="submit" name="submit" class="btn btn-primary" value="Login">
</form>
<?php require_once __DIR__ . '/template/footer.php'; ?>
