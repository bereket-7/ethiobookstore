<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';

$txRef = (string) ($_GET['tx_ref'] ?? $_GET['trx_ref'] ?? '');
$conn = db();
$order = $txRef !== '' ? getOrderByTxRef($conn, $txRef) : null;

// Optionally re-verify with Chapa on return
if ($order && $order['payment_status'] !== 'paid') {
	$chapa = ChapaClient::fromConfig();
	if ($chapa->isConfigured()) {
		$verified = $chapa->verify($txRef);
		if (!empty($verified['ok'])) {
			markOrderPaid($conn, (int) $order['orderid']);
			$order = getOrderById($conn, (int) $order['orderid']);
		}
	}
}

$title = 'Payment';
require_once __DIR__ . '/template/header.php';

if (!$order) {
	echo '<p class="text-danger">Order not found.</p>';
} elseif ($order['payment_status'] === 'paid') {
	echo '<p class="lead text-success">' . e(t('order_paid')) . ' Order #' . (int) $order['orderid'] . '</p>';
} else {
	echo '<p class="lead text-warning">' . e(t('order_success')) . ' Order #' . (int) $order['orderid'] . ' — status: ' . e($order['payment_status']) . '</p>';
}
?>
<p><a class="btn btn-primary" href="books.php"><?php echo e(t('continue_shopping')); ?></a>
<?php if (customer_logged_in()): ?>
<a class="btn btn-default" href="my_orders.php"><?php echo e(t('nav_orders')); ?></a>
<?php endif; ?>
</p>
<?php require_once __DIR__ . '/template/footer.php'; ?>
