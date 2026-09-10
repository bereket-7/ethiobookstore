<?php

declare(strict_types=1);

require_once __DIR__ . '/functions/admin.php';

$conn = db();
$q = trim((string) ($_GET['q'] ?? ''));
$page = max(1, (int) ($_GET['page'] ?? 1));
$data = searchBooks($conn, $q, null, $page, 20);
$pager = $data['pagination'];

$title = 'List book';
require_once __DIR__ . '/template/header.php';
?>
<p>
	<a href="admin_dashboard.php">Dashboard</a> |
	<a href="admin_add.php">Add new book</a> |
	<a href="admin_signout.php" class="btn btn-primary">Logout</a>
</p>
<form method="get" class="form-inline" style="margin-bottom:12px;">
	<input type="text" name="q" class="form-control" value="<?php echo e($q); ?>" placeholder="Search books">
	<button class="btn btn-default" type="submit">Search</button>
</form>
<table class="table" style="margin-top: 20px">
	<tr>
		<th>ISBN</th>
		<th>Title</th>
		<th>Author</th>
		<th>Image</th>
		<th>Price</th>
		<th>Publisher</th>
		<th>&nbsp;</th>
		<th>&nbsp;</th>
	</tr>
	<?php foreach ($data['rows'] as $row): ?>
		<tr>
			<td><?php echo e($row['book_isbn']); ?></td>
			<td><?php echo e($row['book_title']); ?></td>
			<td><?php echo e($row['book_author']); ?></td>
			<td><?php echo e($row['book_image']); ?></td>
			<td><?php echo e(money_etb((float) $row['book_price'])); ?></td>
			<td><?php
				$full = getBookByIsbn($conn, $row['book_isbn']);
				echo e($full ? getPubName($conn, (int) $full['publisherid']) : '');
			?></td>
			<td><a href="admin_edit.php?bookisbn=<?php echo e(urlencode($row['book_isbn'])); ?>">Edit</a></td>
			<td>
				<form method="post" action="admin_delete.php" style="display:inline" onsubmit="return confirm('Soft-delete this book?');">
					<?php echo csrf_field(); ?>
					<input type="hidden" name="bookisbn" value="<?php echo e($row['book_isbn']); ?>">
					<button type="submit" class="btn btn-link" style="padding:0;color:#a94442;">Delete</button>
				</form>
			</td>
		</tr>
	<?php endforeach; ?>
</table>
<?php if ($pager['total_pages'] > 1): ?>
	<ul class="pager">
		<?php if ($pager['page'] > 1): ?><li><a href="?q=<?php echo e(urlencode($q)); ?>&page=<?php echo $pager['page'] - 1; ?>">Prev</a></li><?php endif; ?>
		<?php if ($pager['page'] < $pager['total_pages']): ?><li><a href="?q=<?php echo e(urlencode($q)); ?>&page=<?php echo $pager['page'] + 1; ?>">Next</a></li><?php endif; ?>
	</ul>
<?php endif; ?>
<?php require_once __DIR__ . '/template/footer.php'; ?>
