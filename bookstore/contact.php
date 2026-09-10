<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';

if (is_post()) {
	require_csrf();
	$name = trim((string) ($_POST['name'] ?? ''));
	$email = trim((string) ($_POST['email'] ?? ''));
	$body = trim((string) ($_POST['body'] ?? ''));
	if ($name === '' || $email === '' || $body === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
		flash_set('error', 'Please fill all fields with a valid email.');
		redirect_local('contact.php');
	}
	$conn = db();
	$stmt = $conn->prepare('INSERT INTO messages (name, email, body) VALUES (?, ?, ?)');
	$stmt->bind_param('sss', $name, $email, $body);
	$stmt->execute();
	$stmt->close();
	flash_set('success', t('message_sent'));
	redirect_local('contact.php');
}

$title = t('nav_contact');
require_once __DIR__ . '/template/header.php';
?>
<div class="row">
	<div class="col-md-3"></div>
	<div class="col-md-6 text-center">
		<form class="form-horizontal" method="post" action="contact.php">
			<?php echo csrf_field(); ?>
			<fieldset>
				<legend><?php echo e(t('contact_legend')); ?></legend>
				<p class="lead"><?php echo e(t('contact_lead')); ?></p>
				<div class="form-group">
					<label class="col-lg-2 control-label">Name</label>
					<div class="col-lg-10">
						<input type="text" class="form-control" name="name" required>
					</div>
				</div>
				<div class="form-group">
					<label class="col-lg-2 control-label">Email</label>
					<div class="col-lg-10">
						<input type="email" class="form-control" name="email" required>
					</div>
				</div>
				<div class="form-group">
					<label class="col-lg-2 control-label">Message</label>
					<div class="col-lg-10">
						<textarea class="form-control" rows="4" name="body" required></textarea>
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-10 col-lg-offset-2">
						<button type="reset" class="btn btn-default"><?php echo e(t('cancel')); ?></button>
						<button type="submit" class="btn btn-primary"><?php echo e(t('send')); ?></button>
					</div>
				</div>
			</fieldset>
		</form>
	</div>
</div>
<?php require_once __DIR__ . '/template/footer.php'; ?>
