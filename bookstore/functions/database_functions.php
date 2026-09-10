<?php

declare(strict_types=1);

/**
 * Backward-compatible wrapper used by older includes.
 */
function db_connect(): mysqli
{
	return db();
}

function select4LatestBook(mysqli $conn): array
{
	$query = "SELECT book_isbn, book_image, book_title, book_price, book_author
		FROM books
		WHERE deleted_at IS NULL
		ORDER BY book_isbn DESC
		LIMIT 4";
	$result = $conn->query($query);
	if (!$result) {
		app_error('Cannot retrieve latest books: ' . $conn->error);
	}
	$rows = [];
	while ($row = $result->fetch_assoc()) {
		$rows[] = $row;
	}
	return $rows;
}

function getBookByIsbn(mysqli $conn, string $isbn): ?array
{
	$stmt = $conn->prepare(
		'SELECT book_isbn, book_title, book_author, book_price, book_image, book_descr, publisherid, category_id, language
		 FROM books WHERE book_isbn = ? AND deleted_at IS NULL LIMIT 1'
	);
	$stmt->bind_param('s', $isbn);
	$stmt->execute();
	$result = $stmt->get_result();
	$row = $result->fetch_assoc();
	$stmt->close();
	return $row ?: null;
}

function getbookprice(string $isbn): ?float
{
	$conn = db();
	$stmt = $conn->prepare('SELECT book_price FROM books WHERE book_isbn = ? AND deleted_at IS NULL LIMIT 1');
	$stmt->bind_param('s', $isbn);
	$stmt->execute();
	$result = $stmt->get_result();
	$row = $result->fetch_assoc();
	$stmt->close();
	return $row ? (float) $row['book_price'] : null;
}

function getPubName(mysqli $conn, int|string $pubid): string
{
	$pubid = (int) $pubid;
	$stmt = $conn->prepare('SELECT publisher_name FROM publisher WHERE publisherid = ? LIMIT 1');
	$stmt->bind_param('i', $pubid);
	$stmt->execute();
	$result = $stmt->get_result();
	$row = $result->fetch_assoc();
	$stmt->close();
	if (!$row) {
		app_error('Publisher not found');
	}
	return $row['publisher_name'];
}

function getOrCreatePublisherId(mysqli $conn, string $publisherName): int
{
	$stmt = $conn->prepare('SELECT publisherid FROM publisher WHERE publisher_name = ? LIMIT 1');
	$stmt->bind_param('s', $publisherName);
	$stmt->execute();
	$result = $stmt->get_result();
	$row = $result->fetch_assoc();
	$stmt->close();
	if ($row) {
		return (int) $row['publisherid'];
	}

	$insert = $conn->prepare('INSERT INTO publisher (publisher_name) VALUES (?)');
	$insert->bind_param('s', $publisherName);
	if (!$insert->execute()) {
		app_error('Cannot add publisher: ' . $insert->error);
	}
	$id = (int) $insert->insert_id;
	$insert->close();
	return $id;
}

function getCustomerId(string $name, string $address, string $city, string $zip_code, string $country): ?int
{
	$conn = db();
	$stmt = $conn->prepare(
		'SELECT customerid FROM customers
		 WHERE name = ? AND address = ? AND city = ? AND zip_code = ? AND country = ?
		 LIMIT 1'
	);
	$stmt->bind_param('sssss', $name, $address, $city, $zip_code, $country);
	$stmt->execute();
	$result = $stmt->get_result();
	$row = $result->fetch_assoc();
	$stmt->close();
	return $row ? (int) $row['customerid'] : null;
}

function setCustomerId(
	string $name,
	string $address,
	string $city,
	string $zip_code,
	string $country,
	?int $userId = null,
	?string $email = null
): int {
	$conn = db();
	if ($userId === null) {
		$emailVal = $email;
		$stmt = $conn->prepare(
			'INSERT INTO customers (user_id, name, address, city, zip_code, country, email)
			 VALUES (NULL, ?, ?, ?, ?, ?, ?)'
		);
		$stmt->bind_param('ssssss', $name, $address, $city, $zip_code, $country, $emailVal);
	} else {
		$emailVal = $email;
		$stmt = $conn->prepare(
			'INSERT INTO customers (user_id, name, address, city, zip_code, country, email)
			 VALUES (?, ?, ?, ?, ?, ?, ?)'
		);
		$stmt->bind_param('issssss', $userId, $name, $address, $city, $zip_code, $country, $emailVal);
	}
	if (!$stmt->execute()) {
		app_error('Cannot create customer: ' . $stmt->error);
	}
	$id = (int) $stmt->insert_id;
	$stmt->close();
	return $id;
}

/**
 * Create order + items in a transaction. Returns new order id.
 *
 * @param array<string,int> $cart isbn => qty
 */
function createOrderWithItems(
	mysqli $conn,
	array $cart,
	float $subtotal,
	float $deliveryFee,
	array $ship,
	?int $userId = null,
	?string $email = null
): int {
	$conn->begin_transaction();
	try {
		$customerId = getCustomerId(
			$ship['name'],
			$ship['address'],
			$ship['city'],
			$ship['zip_code'],
			$ship['country']
		);
		if ($customerId === null) {
			$customerId = setCustomerId(
				$ship['name'],
				$ship['address'],
				$ship['city'],
				$ship['zip_code'],
				$ship['country'],
				$userId,
				$email
			);
		}

		$total = $subtotal + $deliveryFee;
		$status = 'new';
		$paymentStatus = 'pending';
		$txRef = 'eb_' . bin2hex(random_bytes(8));

		if ($userId === null) {
			$stmt = $conn->prepare(
				'INSERT INTO orders
				(customerid, user_id, amount, delivery_fee, ship_name, ship_address, ship_city, ship_zip_code, ship_country, status, payment_status, payment_tx_ref)
				VALUES (?, NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
			);
			$stmt->bind_param(
				'iddssssssss',
				$customerId,
				$total,
				$deliveryFee,
				$ship['name'],
				$ship['address'],
				$ship['city'],
				$ship['zip_code'],
				$ship['country'],
				$status,
				$paymentStatus,
				$txRef
			);
		} else {
			$stmt = $conn->prepare(
				'INSERT INTO orders
				(customerid, user_id, amount, delivery_fee, ship_name, ship_address, ship_city, ship_zip_code, ship_country, status, payment_status, payment_tx_ref)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
			);
			$stmt->bind_param(
				'iiddssssssss',
				$customerId,
				$userId,
				$total,
				$deliveryFee,
				$ship['name'],
				$ship['address'],
				$ship['city'],
				$ship['zip_code'],
				$ship['country'],
				$status,
				$paymentStatus,
				$txRef
			);
		}
		if (!$stmt->execute()) {
			throw new RuntimeException($stmt->error);
		}
		$orderId = (int) $stmt->insert_id;
		$stmt->close();

		$itemStmt = $conn->prepare(
			'INSERT INTO order_items (orderid, book_isbn, item_price, quantity) VALUES (?, ?, ?, ?)'
		);
		foreach ($cart as $isbn => $qty) {
			$qty = (int) $qty;
			if ($qty < 1) {
				continue;
			}
			$price = getbookprice((string) $isbn);
			if ($price === null) {
				throw new RuntimeException('Unknown book: ' . $isbn);
			}
			$isbnStr = (string) $isbn;
			$itemStmt->bind_param('isdi', $orderId, $isbnStr, $price, $qty);
			if (!$itemStmt->execute()) {
				throw new RuntimeException($itemStmt->error);
			}
		}
		$itemStmt->close();
		$conn->commit();
		return $orderId;
	} catch (Throwable $e) {
		$conn->rollback();
		app_log('error', 'create_order_failed', ['error' => $e->getMessage()]);
		throw $e;
	}
}

function getOrderById(mysqli $conn, int $orderId): ?array
{
	$stmt = $conn->prepare('SELECT * FROM orders WHERE orderid = ? LIMIT 1');
	$stmt->bind_param('i', $orderId);
	$stmt->execute();
	$row = $stmt->get_result()->fetch_assoc();
	$stmt->close();
	return $row ?: null;
}

function getOrderByTxRef(mysqli $conn, string $txRef): ?array
{
	$stmt = $conn->prepare('SELECT * FROM orders WHERE payment_tx_ref = ? LIMIT 1');
	$stmt->bind_param('s', $txRef);
	$stmt->execute();
	$row = $stmt->get_result()->fetch_assoc();
	$stmt->close();
	return $row ?: null;
}

function markOrderPaid(mysqli $conn, int $orderId): bool
{
	$stmt = $conn->prepare(
		"UPDATE orders SET payment_status = 'paid', status = 'paid' WHERE orderid = ? AND payment_status <> 'paid'"
	);
	$stmt->bind_param('i', $orderId);
	$ok = $stmt->execute();
	$stmt->close();
	return $ok;
}

function getAll(mysqli $conn, bool $includeDeleted = false): mysqli_result
{
	$sql = 'SELECT * FROM books';
	if (!$includeDeleted) {
		$sql .= ' WHERE deleted_at IS NULL';
	}
	$sql .= ' ORDER BY book_isbn DESC';
	$result = $conn->query($sql);
	if (!$result) {
		app_error('Cannot retrieve books: ' . $conn->error);
	}
	return $result;
}

/**
 * @return array{rows: list<array>, pagination: array}
 */
function searchBooks(
	mysqli $conn,
	string $q = '',
	?int $categoryId = null,
	int $page = 1,
	int $perPage = 12
): array {
	$where = ['deleted_at IS NULL'];
	$types = '';
	$params = [];

	if ($q !== '') {
		$where[] = '(book_title LIKE ? OR book_author LIKE ? OR book_isbn LIKE ?)';
		$like = '%' . $q . '%';
		$types .= 'sss';
		$params[] = $like;
		$params[] = $like;
		$params[] = $like;
	}
	if ($categoryId) {
		$where[] = 'category_id = ?';
		$types .= 'i';
		$params[] = $categoryId;
	}

	$whereSql = implode(' AND ', $where);
	$countSql = "SELECT COUNT(*) AS c FROM books WHERE $whereSql";
	$countStmt = $conn->prepare($countSql);
	if ($types !== '') {
		$countStmt->bind_param($types, ...$params);
	}
	$countStmt->execute();
	$total = (int) ($countStmt->get_result()->fetch_assoc()['c'] ?? 0);
	$countStmt->close();

	$pager = pagination($total, $page, $perPage);
	$sql = "SELECT book_isbn, book_title, book_author, book_image, book_price, category_id, language
		FROM books WHERE $whereSql ORDER BY book_isbn DESC LIMIT ? OFFSET ?";
	$stmt = $conn->prepare($sql);
	$limit = $pager['per_page'];
	$offset = $pager['offset'];
	if ($types !== '') {
		$types2 = $types . 'ii';
		$params2 = [...$params, $limit, $offset];
		$stmt->bind_param($types2, ...$params2);
	} else {
		$stmt->bind_param('ii', $limit, $offset);
	}
	$stmt->execute();
	$result = $stmt->get_result();
	$rows = [];
	while ($row = $result->fetch_assoc()) {
		$rows[] = $row;
	}
	$stmt->close();

	return ['rows' => $rows, 'pagination' => $pager];
}

function getCategories(mysqli $conn): array
{
	$result = $conn->query('SELECT id, name, slug FROM categories ORDER BY name');
	$rows = [];
	if ($result) {
		while ($row = $result->fetch_assoc()) {
			$rows[] = $row;
		}
	}
	return $rows;
}

function getPublishersWithCounts(mysqli $conn): array
{
	$sql = 'SELECT p.publisherid, p.publisher_name, COUNT(b.book_isbn) AS book_count
		FROM publisher p
		LEFT JOIN books b ON b.publisherid = p.publisherid AND b.deleted_at IS NULL
		GROUP BY p.publisherid, p.publisher_name
		ORDER BY p.publisherid';
	$result = $conn->query($sql);
	$rows = [];
	if ($result) {
		while ($row = $result->fetch_assoc()) {
			$rows[] = $row;
		}
	}
	return $rows;
}

function softDeleteBook(mysqli $conn, string $isbn): bool
{
	$stmt = $conn->prepare('UPDATE books SET deleted_at = NOW() WHERE book_isbn = ? AND deleted_at IS NULL');
	$stmt->bind_param('s', $isbn);
	$ok = $stmt->execute();
	$stmt->close();
	return $ok;
}

function bookHasOrders(mysqli $conn, string $isbn): bool
{
	$stmt = $conn->prepare('SELECT 1 FROM order_items WHERE book_isbn = ? LIMIT 1');
	$stmt->bind_param('s', $isbn);
	$stmt->execute();
	$exists = (bool) $stmt->get_result()->fetch_row();
	$stmt->close();
	return $exists;
}
