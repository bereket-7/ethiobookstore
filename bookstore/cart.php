<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';

if (is_post()) {
	require_csrf();
}

if (isset($_POST['bookisbn'])) {
	$book_isbn = (string) $_POST['bookisbn'];
	if (!isset($_SESSION['cart'])) {
		$_SESSION['cart'] = [];
		$_SESSION['total_items'] = 0;
		$_SESSION['total_price'] = 0.0;
	}
	if (!isset($_SESSION['cart'][$book_isbn])) {
		$_SESSION['cart'][$book_isbn] = 1;
	} elseif (isset($_POST['cart'])) {
		$_SESSION['cart'][$book_isbn]++;
	}
}

if (isset($_POST['save_change']) && isset($_SESSION['cart'])) {
	require_csrf();
	$_SESSION['cart'] = normalize_cart_quantities($_POST);
}

$title = t('nav_cart');
require_once __DIR__ . '/template/header.php';

$cart = $_SESSION['cart'] ?? [];
if ($cart !== []) {
	$_SESSION['total_price'] = total_price($cart);
	$_SESSION['total_items'] = total_items($cart);
	$conn = db();
?>
	<form action="cart.php" method="post">
		<?php echo csrf_field(); ?>
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
					<td><input type="number" min="0" value="<?php echo (int) $qty; ?>" size="4" name="<?php echo e((string) $isbn); ?>" class="form-control" style="width:80px"></td>
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
		<input type="submit" class="btn btn-primary" name="save_change" value="<?php echo e(t('save_change')); ?>">
	</form>
	<br>
	<a href="checkout.php" class="btn btn-primary"><?php echo e(t('checkout')); ?></a>
	<a href="books.php" class="btn btn-default"><?php echo e(t('continue_shopping')); ?></a>
<?php
} else {
	echo '<p class="text-warning">' . e(t('empty_cart')) . '</p>';
}
require_once __DIR__ . '/template/footer.php';
