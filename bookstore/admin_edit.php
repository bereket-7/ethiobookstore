<?php

declare(strict_types=1);

require_once __DIR__ . '/functions/admin.php';

$conn = db();
$book_isbn = (string) ($_GET['bookisbn'] ?? '');
if ($book_isbn === '') {
	flash_set('error', 'Empty ISBN');
	redirect_local('admin_book.php');
}

$row = getBookByIsbn($conn, $book_isbn);
if (!$row) {
	// allow editing soft-deleted? no — only active
	flash_set('error', 'Book not found');
	redirect_local('admin_book.php');
}

$categories = getCategories($conn);
$title = 'Edit book';
require_once __DIR__ . '/template/header.php';
?>
<form method="post" action="edit_book.php" enctype="multipart/form-data">
	<?php echo csrf_field(); ?>
	<table class="table">
		<tr>
			<th>ISBN</th>
			<td><input type="text" name="isbn" class="form-control" value="<?php echo e($row['book_isbn']); ?>" readonly></td>
		</tr>
		<tr>
			<th>Title</th>
			<td><input type="text" name="title" class="form-control" value="<?php echo e($row['book_title']); ?>" required></td>
		</tr>
		<tr>
			<th>Author</th>
			<td><input type="text" name="author" class="form-control" value="<?php echo e($row['book_author']); ?>" required></td>
		</tr>
		<tr>
			<th>Image</th>
			<td>
				<p>Current: <?php echo e($row['book_image']); ?></p>
				<input type="file" name="image" accept=".jpg,.jpeg,.png,.webp">
			</td>
		</tr>
		<tr>
			<th>Description</th>
			<td><textarea name="descr" class="form-control" rows="5"><?php echo e($row['book_descr']); ?></textarea></td>
		</tr>
		<tr>
			<th>Price</th>
			<td><input type="number" step="0.01" name="price" class="form-control" value="<?php echo e((string) $row['book_price']); ?>" required></td>
		</tr>
		<tr>
			<th>Publisher</th>
			<td><input type="text" name="publisher" class="form-control" value="<?php echo e(getPubName($conn, (int) $row['publisherid'])); ?>" required></td>
		</tr>
		<tr>
			<th>Language</th>
			<td><input type="text" name="language" class="form-control" value="<?php echo e($row['language']); ?>"></td>
		</tr>
		<tr>
			<th>Category</th>
			<td>
				<select name="category_id" class="form-control">
					<option value="">—</option>
					<?php foreach ($categories as $cat): ?>
						<option value="<?php echo (int) $cat['id']; ?>" <?php echo (int) $row['category_id'] === (int) $cat['id'] ? 'selected' : ''; ?>>
							<?php echo e($cat['name']); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
	</table>
	<input type="submit" name="save_change" value="Save Change" class="btn btn-primary">
	<a href="admin_book.php" class="btn btn-default">Back</a>
</form>
<?php require_once __DIR__ . '/template/footer.php'; ?>
