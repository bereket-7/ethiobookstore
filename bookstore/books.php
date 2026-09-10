<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';

$conn = db();
$q = trim((string) ($_GET['q'] ?? ''));
$categoryId = isset($_GET['category']) && $_GET['category'] !== '' ? (int) $_GET['category'] : null;
$page = max(1, (int) ($_GET['page'] ?? 1));
$data = searchBooks($conn, $q, $categoryId, $page, 12);
$categories = getCategories($conn);
$pager = $data['pagination'];

$title = t('full_catalog');
require_once __DIR__ . '/template/header.php';
?>
<p class="lead text-center text-muted"><?php echo e(t('full_catalog')); ?></p>

<form class="form-inline" method="get" action="books.php" style="margin-bottom: 20px;">
  <input type="text" name="q" class="form-control" placeholder="<?php echo e(t('search_placeholder')); ?>" value="<?php echo e($q); ?>">
  <select name="category" class="form-control">
    <option value=""><?php echo e(t('all_categories')); ?></option>
    <?php foreach ($categories as $cat): ?>
      <option value="<?php echo (int) $cat['id']; ?>" <?php echo $categoryId === (int) $cat['id'] ? 'selected' : ''; ?>>
        <?php echo e($cat['name']); ?>
      </option>
    <?php endforeach; ?>
  </select>
  <button type="submit" class="btn btn-primary"><?php echo e(t('search')); ?></button>
</form>

<div class="row">
  <?php foreach ($data['rows'] as $book): ?>
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

<?php if ($pager['total_pages'] > 1): ?>
  <nav>
    <ul class="pager">
      <?php if ($pager['page'] > 1): ?>
        <li class="previous"><a href="?q=<?php echo e(urlencode($q)); ?>&category=<?php echo e((string) ($categoryId ?? '')); ?>&page=<?php echo $pager['page'] - 1; ?>"><?php echo e(t('prev')); ?></a></li>
      <?php endif; ?>
      <?php if ($pager['page'] < $pager['total_pages']): ?>
        <li class="next"><a href="?q=<?php echo e(urlencode($q)); ?>&category=<?php echo e((string) ($categoryId ?? '')); ?>&page=<?php echo $pager['page'] + 1; ?>"><?php echo e(t('next')); ?></a></li>
      <?php endif; ?>
    </ul>
  </nav>
<?php endif; ?>

<?php require_once __DIR__ . '/template/footer.php'; ?>
