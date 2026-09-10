<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';
require_admin();

$conn = db();
$ordersToday = (int) ($conn->query("SELECT COUNT(*) c FROM orders WHERE DATE(`date`) = CURDATE()")->fetch_assoc()['c'] ?? 0);
$revenue = (float) ($conn->query("SELECT COALESCE(SUM(amount),0) s FROM orders WHERE payment_status = 'paid'")->fetch_assoc()['s'] ?? 0);
$pending = (int) ($conn->query("SELECT COUNT(*) c FROM orders WHERE payment_status = 'pending'")->fetch_assoc()['c'] ?? 0);
$books = (int) ($conn->query("SELECT COUNT(*) c FROM books WHERE deleted_at IS NULL")->fetch_assoc()['c'] ?? 0);

$title = 'Admin dashboard';
require_once __DIR__ . '/template/header.php';
?>
<p class="lead">Admin dashboard</p>
<p>
	<a class="btn btn-default" href="admin_book.php">Books</a>
	<a class="btn btn-default" href="admin_orders.php">Orders</a>
	<a class="btn btn-default" href="admin_customers.php">Customers</a>
	<a class="btn btn-primary" href="admin_signout.php">Logout</a>
</p>
<div class="row">
	<div class="col-md-3"><div class="well"><h4>Orders today</h4><p class="lead"><?php echo $ordersToday; ?></p></div></div>
	<div class="col-md-3"><div class="well"><h4>Paid revenue</h4><p class="lead"><?php echo e(money_etb($revenue)); ?></p></div></div>
	<div class="col-md-3"><div class="well"><h4>Pending payment</h4><p class="lead"><?php echo $pending; ?></p></div></div>
	<div class="col-md-3"><div class="well"><h4>Active books</h4><p class="lead"><?php echo $books; ?></p></div></div>
</div>
<?php require_once __DIR__ . '/template/footer.php'; ?>
