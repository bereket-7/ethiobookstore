<?php

declare(strict_types=1);

require_once __DIR__ . '/functions/admin.php';

$conn = db();
$status = (string) ($_GET['status'] ?? '');
$sql = 'SELECT orderid, amount, date, status, payment_status, ship_name FROM orders';
$params = [];
$types = '';
if (in_array($status, ['new', 'paid', 'shipped', 'cancelled'], true)) {
	$sql .= ' WHERE status = ?';
	$types = 's';
	$params[] = $status;
}
$sql .= ' ORDER BY orderid DESC LIMIT 100';
$stmt = $conn->prepare($sql);
if ($types !== '') {
	$stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$title = 'Orders';
require_once __DIR__ . '/template/header.php';
?>
<p>
	<a href="admin_dashboard.php">Dashboard</a> |
	<a href="admin_orders.php">All</a> |
	<a href="admin_orders.php?status=paid">Paid</a> |
	<a href="admin_orders.php?status=shipped">Shipped</a>
</p>
<table class="table">
	<tr>
		<th>ID</th>
		<th>Date</th>
		<th>Customer</th>
		<th>Amount</th>
		<th>Status</th>
		<th>Payment</th>
		<th></th>
	</tr>
	<?php while ($row = $result->fetch_assoc()): ?>
		<tr>
			<td>#<?php echo (int) $row['orderid']; ?></td>
			<td><?php echo e($row['date']); ?></td>
			<td><?php echo e($row['ship_name']); ?></td>
			<td><?php echo e(money_etb((float) $row['amount'])); ?></td>
			<td><?php echo e($row['status']); ?></td>
			<td><?php echo e($row['payment_status']); ?></td>
			<td><a href="admin_order.php?id=<?php echo (int) $row['orderid']; ?>">View</a></td>
		</tr>
	<?php endwhile; ?>
</table>
<?php
$stmt->close();
require_once __DIR__ . '/template/footer.php';
?>
