<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';

if (!is_post()) {
	redirect_local('checkout.php');
}
require_csrf();

$required = ['name', 'email', 'address', 'city', 'zip_code', 'country'];
$ship = [];
foreach ($required as $field) {
	$value = trim((string) ($_POST[$field] ?? ''));
	if ($value === '') {
		flash_set('error', 'All fields have to be filled');
		redirect_local('checkout.php');
	}
	$ship[$field] = $value;
}
if (!filter_var($ship['email'], FILTER_VALIDATE_EMAIL)) {
	flash_set('error', 'Invalid email address');
	redirect_local('checkout.php');
}

$_SESSION['ship'] = $ship;
$cart = $_SESSION['cart'] ?? [];
if ($cart === []) {
	flash_set('error', t('empty_cart'));
	redirect_local('cart.php');
}

$conn = db();
$_SESSION['total_price'] = total_price($cart);
$_SESSION['total_items'] = total_items($cart);
$fee = delivery_fee();
$grand = (float) $_SESSION['total_price'] + $fee;

$title = t('purchase');
require_once __DIR__ . '/template/header.php';
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
		<td><?php echo e(t('delivery')); ?></td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td><?php echo e(money_etb($fee)); ?></td>
	</tr>
	<tr>
		<th><?php echo e(t('total_with_delivery')); ?></th>
		<th>&nbsp;</th>
		<th>&nbsp;</th>
		<th><?php echo e(money_etb($grand)); ?></th>
	</tr>
</table>

<form method="post" action="process.php">
	<?php echo csrf_field(); ?>
	<p class="lead"><?php echo e(t('pay_chapa')); ?></p>
	<button type="submit" class="btn btn-primary"><?php echo e(t('pay_chapa')); ?></button>
	<a href="books.php" class="btn btn-default"><?php echo e(t('continue_shopping')); ?></a>
</form>
<?php require_once __DIR__ . '/template/footer.php'; ?>
