<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';

$raw = file_get_contents('php://input') ?: '';
$signature = $_SERVER['HTTP_CHAPA_SIGNATURE'] ?? $_SERVER['HTTP_X_CHAPA_SIGNATURE'] ?? null;
$chapa = ChapaClient::fromConfig();
$webhookSecret = (string) config('chapa.webhook_secret');
if ($webhookSecret !== '' && !$chapa->verifyWebhookSignature($raw, is_string($signature) ? $signature : null)) {
	http_response_code(401);
	app_log('error', 'chapa_webhook_bad_signature');
	exit('invalid signature');
}

$payload = json_decode($raw, true);
if (!is_array($payload)) {
	$payload = $_POST;
}

$txRef = (string) ($payload['tx_ref'] ?? $payload['data']['tx_ref'] ?? $_GET['trx_ref'] ?? $_GET['tx_ref'] ?? '');
if ($txRef === '') {
	http_response_code(400);
	exit('missing tx_ref');
}

$conn = db();
$order = getOrderByTxRef($conn, $txRef);
if (!$order) {
	http_response_code(404);
	exit('order not found');
}

$verified = $chapa->isConfigured() ? $chapa->verify($txRef) : ['ok' => config('app.debug')];
if (!empty($verified['ok'])) {
	if (markOrderPaid($conn, (int) $order['orderid'])) {
		$email = null;
		$custStmt = $conn->prepare('SELECT email FROM customers WHERE customerid = ? LIMIT 1');
		$cid = (int) $order['customerid'];
		$custStmt->bind_param('i', $cid);
		$custStmt->execute();
		$email = $custStmt->get_result()->fetch_assoc()['email'] ?? null;
		$custStmt->close();
		if ($email) {
			send_order_confirmation((string) $email, (int) $order['orderid'], (float) $order['amount']);
		}
	}
	echo 'ok';
	exit;
}

http_response_code(402);
echo 'not paid';
