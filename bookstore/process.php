<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';

if (!is_post()) {
	redirect_local('checkout.php');
}
require_csrf();

$ship = $_SESSION['ship'] ?? null;
$cart = $_SESSION['cart'] ?? [];
if (!$ship || $cart === []) {
	flash_set('error', t('empty_cart'));
	redirect_local('cart.php');
}

$conn = db();
$subtotal = total_price($cart);
$fee = delivery_fee();
$userId = current_user_id();
$email = $ship['email'] ?? ($_SESSION['user_email'] ?? null);

try {
	$orderId = createOrderWithItems($conn, $cart, $subtotal, $fee, $ship, $userId, $email);
} catch (Throwable $e) {
	flash_set('error', 'Could not create order. Please try again.');
	redirect_local('checkout.php');
}

$order = getOrderById($conn, $orderId);
if (!$order) {
	flash_set('error', 'Order missing after create');
	redirect_local('checkout.php');
}

$txRef = (string) $order['payment_tx_ref'];
$amount = (float) $order['amount'];
$chapa = ChapaClient::fromConfig();

// Clear cart after order is persisted
unset($_SESSION['cart'], $_SESSION['total_items'], $_SESSION['total_price'], $_SESSION['ship']);

if ($chapa->isConfigured()) {
	$base = rtrim((string) config('app.url'), '/');
	$callback = $base . '/payment_callback.php';
	$returnUrl = $base . '/payment_return.php?tx_ref=' . urlencode($txRef);
	$init = $chapa->initialize([
		'amount' => number_format($amount, 2, '.', ''),
		'currency' => 'ETB',
		'email' => $email ?: 'customer@example.com',
		'first_name' => $ship['name'],
		'last_name' => 'Customer',
		'tx_ref' => $txRef,
		'callback_url' => $callback,
		'return_url' => $returnUrl,
		'customization' => [
			'title' => 'EthioBookstore',
			'description' => 'Order #' . $orderId,
		],
	]);

	if ($init['ok'] && !empty($init['checkout_url'])) {
		header('Location: ' . $init['checkout_url']);
		exit;
	}
	app_log('error', 'chapa_init_failed', $init);
	flash_set('error', $init['error'] ?? 'Payment init failed. Order saved as pending.');
	redirect_local('payment_return.php?tx_ref=' . urlencode($txRef));
}

// Dev fallback when Chapa is not configured: mark paid immediately
if (config('app.debug')) {
	markOrderPaid($conn, $orderId);
	if ($email) {
		send_order_confirmation((string) $email, $orderId, $amount);
	}
	flash_set('success', t('order_paid') . ' (debug mode, Chapa not configured)');
	redirect_local('payment_return.php?tx_ref=' . urlencode($txRef));
}

flash_set('success', t('order_success'));
redirect_local('payment_return.php?tx_ref=' . urlencode($txRef));
