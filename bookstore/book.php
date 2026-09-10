<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';

$book_isbn = (string) ($_GET['bookisbn'] ?? '');
if ($book_isbn === '') {
	flash_set('error', 'Book not found');
	redirect_local('books.php');
}

$conn = db();
$row = getBookByIsbn($conn, $book_isbn);
if (!$row) {
	flash_set('error', 'Book not found');
	redirect_local('books.php');
}

$title = $row['book_title'];
require_once __DIR__ . '/template/header.php';
?>
<br>
<p class="lead" style="margin: 25px 0">
  <a href="books.php"><?php echo e(t('nav_books')); ?></a><br><br>
  <?php echo e($row['book_title']); ?>
</p>
<div class="row">
  <div class="col-md-3 text-center">
    <img class="img-responsive img-thumbnail" src="./bootstrap/img/<?php echo e($row['book_image']); ?>" alt="<?php echo e($row['book_title']); ?>">
  </div>
  <div class="col-md-6">
    <h4>Description</h4>
    <p><?php echo nl2br(e($row['book_descr'])); ?></p>
    <h4>Details</h4>
    <table class="table">
      <tr><td>ISBN</td><td><?php echo e($row['book_isbn']); ?></td></tr>
      <tr><td>Author</td><td><?php echo e($row['book_author']); ?></td></tr>
      <tr><td><?php echo e(t('price')); ?></td><td><?php echo e(money_etb((float) $row['book_price'])); ?></td></tr>
      <tr><td>Language</td><td><?php echo e($row['language']); ?></td></tr>
    </table>
    <form method="post" action="cart.php">
      <?php echo csrf_field(); ?>
      <input type="hidden" name="bookisbn" value="<?php echo e($book_isbn); ?>">
      <input type="submit" value="<?php echo e(t('add_to_cart')); ?>" name="cart" class="btn btn-primary">
    </form>
  </div>
</div>
<?php require_once __DIR__ . '/template/footer.php'; ?>
