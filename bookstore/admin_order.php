<?php

declare(strict_types=1);

require_once __DIR__ . '/functions/admin.php';

$conn = db();
$id = (int) ($_GET['id'] ?? 0);
$order = $id > 0 ? getOrderById($conn, $id) : null;
if (!$order) {
	flash_set('error', 'Order not found');
	redirect_local('admin_orders.php');
}

if (is_post()) {
	require_csrf();
	$newStatus = (string) ($_POST['status'] ?? '');
	$notes = trim((string) ($_POST['shipment_notes'] ?? ''));
	if (!in_array($newStatus, ['new', 'paid', 'shipped', 'cancelled'], true)) {
		flash_set('error', 'Invalid status');
		redirect_local('admin_order.php?id=' . $id);
	}
	$stmt = $conn->prepare('UPDATE orders SET status = ?, shipment_notes = ? WHERE orderid = ?');
	$stmt->bind_param('ssi', $newStatus, $notes, $id);
	$stmt->execute();
	$stmt->close();
	flash_set('success', 'Order updated');
	redirect_local('admin_order.php?id=' . $id);
}

$items = $conn->prepare(
	'SELECT oi.book_isbn, oi.item_price, oi.quantity, b.book_title
	 FROM order_items oi
	 LEFT JOIN books b ON b.book_isbn = oi.book_isbn
	 WHERE oi.orderid = ?'
);
$items->bind_param('i', $id);
$items->execute();
$itemResult = $items->get_result();

$title = 'Order #' . $id;
require_once __DIR__ . '/template/header.php';
?>
<p><a href="admin_orders.php">Back to orders</a></p>
<h3>Order #<?php echo $id; ?></h3>
<p>
	Amount: <?php echo e(money_etb((float) $order['amount'])); ?><br>
	Payment: <?php echo e($order['payment_status']); ?><br>
	Ship to: <?php echo e($order['ship_name'] . ', ' . $order['ship_address'] . ', ' . $order['ship_city']); ?>
</p>
<table class="table">
	<tr><th>ISBN</th><th>Title</th><th>Price</th><th>Qty</th></tr>
	<?php while ($item = $itemResult->fetch_assoc()): ?>
		<tr>
			<td><?php echo e($item['book_isbn']); ?></td>
			<td><?php echo e($item['book_title'] ?? ''); ?></td>
			<td><?php echo e(money_etb((float) $item['item_price'])); ?></td>
			<td><?php echo (int) $item['quantity']; ?></td>
		</tr>
	<?php endwhile; ?>
</table>
<form method="post" class="form-horizontal">
	<?php echo csrf_field(); ?>
	<div class="form-group">
		<label>Status</label>
		<select name="status" class="form-control" style="max-width:240px">
			<?php foreach (['new', 'paid', 'shipped', 'cancelled'] as $st): ?>
				<option value="<?php echo $st; ?>" <?php echo $order['status'] === $st ? 'selected' : ''; ?>><?php echo $st; ?></option>
			<?php endforeach; ?>
		</select>
	</div>
	<div class="form-group">
		<label>Shipment notes</label>
		<textarea name="shipment_notes" class="form-control" rows="3"><?php echo e($order['shipment_notes'] ?? ''); ?></textarea>
	</div>
	<button class="btn btn-primary" type="submit">Update</button>
</form>
<?php
$items->close();
require_once __DIR__ . '/template/footer.php';
?>
