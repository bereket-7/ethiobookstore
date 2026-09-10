<?php

declare(strict_types=1);

require_once __DIR__ . '/functions/admin.php';

if (!is_post() || !isset($_POST['save_change'])) {
	flash_set('error', 'Invalid edit request');
	redirect_local('admin_book.php');
}
require_csrf();

$isbn = trim((string) ($_POST['isbn'] ?? ''));
$titleVal = trim((string) ($_POST['title'] ?? ''));
$author = trim((string) ($_POST['author'] ?? ''));
$descr = trim((string) ($_POST['descr'] ?? ''));
$price = (float) ($_POST['price'] ?? 0);
$publisher = trim((string) ($_POST['publisher'] ?? ''));
$language = trim((string) ($_POST['language'] ?? 'am'));
$categoryId = ($_POST['category_id'] ?? '') !== '' ? (int) $_POST['category_id'] : null;

if ($isbn === '' || $titleVal === '' || $author === '' || $publisher === '' || $price <= 0) {
	flash_set('error', 'Missing required fields');
	redirect_local('admin_edit.php?bookisbn=' . urlencode($isbn));
}

$conn = db();
$publisherid = getOrCreatePublisherId($conn, $publisher);
$image = null;
if (!empty($_FILES['image']['name'])) {
	$stored = store_book_image($_FILES['image']);
	if (!$stored['ok']) {
		flash_set('error', 'Image upload failed');
		redirect_local('admin_edit.php?bookisbn=' . urlencode($isbn));
	}
	$image = $stored['filename'];
}

if ($image !== null && $categoryId !== null) {
	$stmt = $conn->prepare(
		'UPDATE books SET book_title=?, book_author=?, book_descr=?, book_price=?, publisherid=?, category_id=?, language=?, book_image=? WHERE book_isbn=?'
	);
	$stmt->bind_param('sssdiisss', $titleVal, $author, $descr, $price, $publisherid, $categoryId, $language, $image, $isbn);
} elseif ($image !== null && $categoryId === null) {
	$stmt = $conn->prepare(
		'UPDATE books SET book_title=?, book_author=?, book_descr=?, book_price=?, publisherid=?, category_id=NULL, language=?, book_image=? WHERE book_isbn=?'
	);
	$stmt->bind_param('sssdisss', $titleVal, $author, $descr, $price, $publisherid, $language, $image, $isbn);
} elseif ($image === null && $categoryId !== null) {
	$stmt = $conn->prepare(
		'UPDATE books SET book_title=?, book_author=?, book_descr=?, book_price=?, publisherid=?, category_id=?, language=? WHERE book_isbn=?'
	);
	$stmt->bind_param('sssdiiss', $titleVal, $author, $descr, $price, $publisherid, $categoryId, $language, $isbn);
} else {
	$stmt = $conn->prepare(
		'UPDATE books SET book_title=?, book_author=?, book_descr=?, book_price=?, publisherid=?, category_id=NULL, language=? WHERE book_isbn=?'
	);
	$stmt->bind_param('sssdiss', $titleVal, $author, $descr, $price, $publisherid, $language, $isbn);
}

if (!$stmt->execute()) {
	flash_set('error', 'Update failed: ' . $stmt->error);
	redirect_local('admin_edit.php?bookisbn=' . urlencode($isbn));
}
$stmt->close();
flash_set('success', 'Book updated');
redirect_local('admin_edit.php?bookisbn=' . urlencode($isbn));
