<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';

$pubid = (int) ($_GET['pubid'] ?? 0);
if ($pubid < 1) {
	flash_set('error', 'Wrong publisher');
	redirect_local('publisher_list.php');
}

$conn = db();
$pubName = getPubName($conn, $pubid);
$stmt = $conn->prepare(
	'SELECT book_isbn, book_title, book_image, book_price FROM books WHERE publisherid = ? AND deleted_at IS NULL'
);
$stmt->bind_param('i', $pubid);
$stmt->execute();
$result = $stmt->get_result();

$title = $pubName;
require_once __DIR__ . '/template/header.php';
?>
<p class="lead"><a href="publisher_list.php"><?php echo e(t('nav_publishers')); ?></a> &gt; <?php echo e($pubName); ?></p>
<?php if ($result->num_rows === 0): ?>
	<p class="text-warning">No books for this publisher yet.</p>
<?php endif; ?>
<?php while ($row = $result->fetch_assoc()): ?>
	<div class="row" style="margin-bottom: 16px;">
		<div class="col-md-3">
			<img class="img-responsive img-thumbnail" src="./bootstrap/img/<?php echo e($row['book_image']); ?>" alt="<?php echo e($row['book_title']); ?>">
		</div>
		<div class="col-md-7">
			<h4><?php echo e($row['book_title']); ?></h4>
			<p><?php echo e(money_etb((float) $row['book_price'])); ?></p>
			<a href="book.php?bookisbn=<?php echo e(urlencode($row['book_isbn'])); ?>" class="btn btn-primary">More</a>
		</div>
	</div>
<?php endwhile;
$stmt->close();
require_once __DIR__ . '/template/footer.php';
?>
