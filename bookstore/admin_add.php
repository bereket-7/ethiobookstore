<?php

declare(strict_types=1);

require_once __DIR__ . '/functions/admin.php';

$conn = db();

if (isset($_POST['add'])) {
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
		flash_set('error', 'ISBN, title, author, publisher and price are required.');
		redirect_local('admin_add.php');
	}

	$image = null;
	if (!empty($_FILES['image']['name'])) {
		$stored = store_book_image($_FILES['image']);
		if (!$stored['ok']) {
			flash_set('error', 'Image upload failed: ' . ($stored['error'] ?? 'unknown'));
			redirect_local('admin_add.php');
		}
		$image = $stored['filename'];
	}

	$publisherid = getOrCreatePublisherId($conn, $publisher);
	if ($categoryId === null) {
		$stmt = $conn->prepare(
			'INSERT INTO books (book_isbn, book_title, book_author, book_image, book_descr, book_price, publisherid, category_id, language)
			 VALUES (?, ?, ?, ?, ?, ?, ?, NULL, ?)'
		);
		$stmt->bind_param('sssssdis', $isbn, $titleVal, $author, $image, $descr, $price, $publisherid, $language);
	} else {
		$stmt = $conn->prepare(
			'INSERT INTO books (book_isbn, book_title, book_author, book_image, book_descr, book_price, publisherid, category_id, language)
			 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
		);
		$stmt->bind_param('sssssdiis', $isbn, $titleVal, $author, $image, $descr, $price, $publisherid, $categoryId, $language);
	}
	if (!$stmt->execute()) {
		flash_set('error', 'Cannot add book: ' . $stmt->error);
		redirect_local('admin_add.php');
	}
	$stmt->close();
	flash_set('success', 'Book added');
	redirect_local('admin_book.php');
}

$categories = getCategories($conn);
$title = 'Add new book';
require_once __DIR__ . '/template/header.php';
?>
<form method="post" action="admin_add.php" enctype="multipart/form-data">
	<?php echo csrf_field(); ?>
	<table class="table">
		<tr><th>ISBN</th><td><input type="text" name="isbn" class="form-control" required></td></tr>
		<tr><th>Title</th><td><input type="text" name="title" class="form-control" required></td></tr>
		<tr><th>Author</th><td><input type="text" name="author" class="form-control" required></td></tr>
		<tr><th>Image</th><td><input type="file" name="image" accept=".jpg,.jpeg,.png,.webp"></td></tr>
		<tr><th>Description</th><td><textarea name="descr" class="form-control" rows="5"></textarea></td></tr>
		<tr><th>Price</th><td><input type="number" step="0.01" min="0.01" name="price" class="form-control" required></td></tr>
		<tr><th>Publisher</th><td><input type="text" name="publisher" class="form-control" required></td></tr>
		<tr><th>Language</th><td><input type="text" name="language" class="form-control" value="am"></td></tr>
		<tr>
			<th>Category</th>
			<td>
				<select name="category_id" class="form-control">
					<option value="">—</option>
					<?php foreach ($categories as $cat): ?>
						<option value="<?php echo (int) $cat['id']; ?>"><?php echo e($cat['name']); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
	</table>
	<input type="submit" name="add" value="Add" class="btn btn-primary">
	<a href="admin_book.php" class="btn btn-default">Back</a>
</form>
<?php require_once __DIR__ . '/template/footer.php'; ?>
