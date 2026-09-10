<?php

declare(strict_types=1);

/**
 * Optional integration test — skipped unless DB is available and TEST_DB=1.
 */
use PHPUnit\Framework\TestCase;

final class OrderTransactionTest extends TestCase
{
	protected function setUp(): void
	{
		if (getenv('TEST_DB') !== '1') {
			$this->markTestSkipped('Set TEST_DB=1 with a migrated MySQL to run order integration tests.');
		}
		try {
			db()->query('SELECT 1');
		} catch (Throwable $e) {
			$this->markTestSkipped('Database not available: ' . $e->getMessage());
		}
	}

	public function testCreateOrderReturnsIdAndItems(): void
	{
		$conn = db();
		$book = $conn->query("SELECT book_isbn FROM books WHERE deleted_at IS NULL LIMIT 1")->fetch_assoc();
		$this->assertNotEmpty($book);
		$isbn = $book['book_isbn'];
		$cart = [$isbn => 2];
		$ship = [
			'name' => 'Test User',
			'address' => '1 Test St',
			'city' => 'Addis Ababa',
			'zip_code' => '1000',
			'country' => 'Ethiopia',
		];
		$subtotal = total_price($cart);
		$orderId = createOrderWithItems($conn, $cart, $subtotal, 25.0, $ship, null, 'test@example.com');
		$this->assertGreaterThan(0, $orderId);
		$order = getOrderById($conn, $orderId);
		$this->assertNotNull($order);
		$this->assertSame('pending', $order['payment_status']);
		$stmt = $conn->prepare('SELECT quantity FROM order_items WHERE orderid = ? AND book_isbn = ?');
		$stmt->bind_param('is', $orderId, $isbn);
		$stmt->execute();
		$qty = (int) $stmt->get_result()->fetch_assoc()['quantity'];
		$stmt->close();
		$this->assertSame(2, $qty);
	}
}
