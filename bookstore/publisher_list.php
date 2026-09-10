<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';

$title = t('nav_publishers');
$conn = db();
$rows = getPublishersWithCounts($conn);
require_once __DIR__ . '/template/header.php';
?>
<p class="lead"><?php echo e(t('nav_publishers')); ?></p>
<ul>
	<?php foreach ($rows as $row): ?>
		<li>
			<span class="badge"><?php echo (int) $row['book_count']; ?></span>
			<a href="bookPerPub.php?pubid=<?php echo (int) $row['publisherid']; ?>"><?php echo e($row['publisher_name']); ?></a>
		</li>
	<?php endforeach; ?>
	<li><a href="books.php"><?php echo e(t('full_catalog')); ?></a></li>
</ul>
<?php require_once __DIR__ . '/template/footer.php'; ?>
