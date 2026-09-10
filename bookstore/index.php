<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';

$title = 'Index';
$conn = db();
$row = select4LatestBook($conn);
require_once __DIR__ . '/template/header.php';
?>
<p class="lead text-center text-muted"><?php echo e(t('latest_books')); ?></p>
<div class="row">
  <?php foreach ($row as $book): ?>
    <div class="col-md-3" style="margin-bottom: 24px;">
      <a href="book.php?bookisbn=<?php echo e(urlencode($book['book_isbn'])); ?>">
        <img class="img-responsive img-thumbnail" src="./bootstrap/img/<?php echo e($book['book_image']); ?>" alt="<?php echo e($book['book_title']); ?>">
      </a>
      <h4><?php echo e($book['book_title']); ?></h4>
      <p class="text-muted"><?php echo e($book['book_author']); ?></p>
      <p><strong><?php echo e(money_etb((float) $book['book_price'])); ?></strong></p>
    </div>
  <?php endforeach; ?>
</div>
<?php require_once __DIR__ . '/template/footer.php'; ?>
