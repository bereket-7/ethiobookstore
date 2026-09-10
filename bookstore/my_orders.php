<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';
require_customer();

$conn = db();
$userId = current_user_id();
$stmt = $conn->prepare(
	'SELECT orderid, amount, date, status, payment_status
	 FROM orders WHERE user_id = ? ORDER BY orderid DESC'
);
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();

$title = t('nav_orders');
require_once __DIR__ . '/template/header.php';
?>
<table class="table">
	<tr>
		<th>Order</th>
		<th>Date</th>
		<th>Amount</th>
		<th>Status</th>
		<th>Payment</th>
	</tr>
	<?php while ($row = $result->fetch_assoc()): ?>
		<tr>
			<td>#<?php echo (int) $row['orderid']; ?></td>
			<td><?php echo e($row['date']); ?></td>
			<td><?php echo e(money_etb((float) $row['amount'])); ?></td>
			<td><?php echo e($row['status']); ?></td>
			<td><?php echo e($row['payment_status']); ?></td>
		</tr>
	<?php endwhile; ?>
</table>
<?php
$stmt->close();
require_once __DIR__ . '/template/footer.php';
?>
