<?php

declare(strict_types=1);

require_once __DIR__ . '/functions/admin.php';

$conn = db();
$result = $conn->query(
	'SELECT customerid, name, address, city, country, email FROM customers ORDER BY customerid DESC LIMIT 200'
);

$title = 'Customers';
require_once __DIR__ . '/template/header.php';
?>
<p><a href="admin_dashboard.php">Dashboard</a></p>
<table class="table">
	<tr>
		<th>ID</th>
		<th>Name</th>
		<th>Email</th>
		<th>City</th>
		<th>Country</th>
	</tr>
	<?php while ($row = $result->fetch_assoc()): ?>
		<tr>
			<td><?php echo (int) $row['customerid']; ?></td>
			<td><?php echo e($row['name']); ?></td>
			<td><?php echo e($row['email'] ?? ''); ?></td>
			<td><?php echo e($row['city']); ?></td>
			<td><?php echo e($row['country']); ?></td>
		</tr>
	<?php endwhile; ?>
</table>
<?php require_once __DIR__ . '/template/footer.php'; ?>
