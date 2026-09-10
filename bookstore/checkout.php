<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';

$title = t('checkout');
require_once __DIR__ . '/template/header.php';

$cart = $_SESSION['cart'] ?? [];
if ($cart === []) {
	echo '<p class="text-warning">' . e(t('empty_cart')) . '</p>';
	require_once __DIR__ . '/template/footer.php';
	exit;
}

$conn = db();
$_SESSION['total_price'] = total_price($cart);
$_SESSION['total_items'] = total_items($cart);
$userName = $_SESSION['user_name'] ?? '';
?>
<table class="table">
	<tr>
		<th>Item</th>
		<th><?php echo e(t('price')); ?></th>
		<th>Quantity</th>
		<th>Total</th>
	</tr>
	<?php foreach ($cart as $isbn => $qty):
		$book = getBookByIsbn($conn, (string) $isbn);
		if (!$book) {
			continue;
		}
	?>
		<tr>
			<td><?php echo e($book['book_title'] . ' — ' . $book['book_author']); ?></td>
			<td><?php echo e(money_etb((float) $book['book_price'])); ?></td>
			<td><?php echo (int) $qty; ?></td>
			<td><?php echo e(money_etb((float) $book['book_price'] * (int) $qty)); ?></td>
		</tr>
	<?php endforeach; ?>
	<tr>
		<th>&nbsp;</th>
		<th>&nbsp;</th>
		<th><?php echo (int) $_SESSION['total_items']; ?></th>
		<th><?php echo e(money_etb((float) $_SESSION['total_price'])); ?></th>
	</tr>
</table>

<form method="post" action="purchase.php" class="form-horizontal">
	<?php echo csrf_field(); ?>
	<div class="form-group">
		<label class="control-label col-md-4">Name</label>
		<div class="col-md-4">
			<input type="text" name="name" class="form-control" required value="<?php echo e($userName); ?>">
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-md-4">Email</label>
		<div class="col-md-4">
			<input type="email" name="email" class="form-control" required value="<?php echo e($_SESSION['user_email'] ?? ''); ?>">
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-md-4">Address</label>
		<div class="col-md-4">
			<input type="text" name="address" class="form-control" required>
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-md-4">City</label>
		<div class="col-md-4">
			<input type="text" name="city" class="form-control" required>
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-md-4">Zip Code</label>
		<div class="col-md-4">
			<input type="text" name="zip_code" class="form-control" required>
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-md-4">Country</label>
		<div class="col-md-4">
			<input type="text" name="country" class="form-control" required value="Ethiopia">
		</div>
	</div>
	<div class="form-group">
		<div class="col-md-offset-4 col-md-4">
			<input type="submit" name="submit" value="<?php echo e(t('purchase')); ?>" class="btn btn-primary">
		</div>
	</div>
</form>
<?php require_once __DIR__ . '/template/footer.php'; ?>
