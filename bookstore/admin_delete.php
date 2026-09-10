<?php

declare(strict_types=1);

require_once __DIR__ . '/functions/admin.php';

if (!is_post()) {
	flash_set('error', 'Delete must be POST');
	redirect_local('admin_book.php');
}
require_csrf();

$book_isbn = (string) ($_POST['bookisbn'] ?? '');
if ($book_isbn === '') {
	flash_set('error', 'Missing ISBN');
	redirect_local('admin_book.php');
}

$conn = db();
if (bookHasOrders($conn, $book_isbn)) {
	softDeleteBook($conn, $book_isbn);
	flash_set('success', 'Book soft-deleted (referenced by orders).');
} else {
	softDeleteBook($conn, $book_isbn);
	flash_set('success', 'Book deleted.');
}
redirect_local('admin_book.php');
