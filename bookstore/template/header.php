<!DOCTYPE html>
<html lang="<?php echo e($_SESSION['lang'] ?? config('app.lang', 'am')); ?>">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo e($title ?? t('site_name')); ?></title>
  <link href="./bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="./bootstrap/css/bootstrap-theme.min.css" rel="stylesheet">
  <link href="./bootstrap/css/jumbotron.css" rel="stylesheet">
</head>

<body>
  <nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container">
      <div class="navbar-header">
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
          <span class="sr-only">Toggle navigation</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="index.php"><?php echo e(t('site_name')); ?></a>
      </div>
      <div id="navbar" class="navbar-collapse collapse">
        <ul class="nav navbar-nav navbar-right">
          <li><a href="publisher_list.php"><span class="glyphicon glyphicon-paperclip"></span>&nbsp; <?php echo e(t('nav_publishers')); ?></a></li>
          <li><a href="books.php"><span class="glyphicon glyphicon-book"></span>&nbsp; <?php echo e(t('nav_books')); ?></a></li>
          <li><a href="contact.php"><span class="glyphicon glyphicon-phone-alt"></span>&nbsp; <?php echo e(t('nav_contact')); ?></a></li>
          <li><a href="cart.php"><span class="glyphicon glyphicon-shopping-cart"></span>&nbsp; <?php echo e(t('nav_cart')); ?></a></li>
          <?php if (customer_logged_in()): ?>
            <li><a href="my_orders.php"><?php echo e(t('nav_orders')); ?></a></li>
            <li><a href="logout.php"><?php echo e(t('nav_logout')); ?></a></li>
          <?php else: ?>
            <li><a href="login.php"><?php echo e(t('nav_login')); ?></a></li>
            <li><a href="register.php"><?php echo e(t('nav_register')); ?></a></li>
          <?php endif; ?>
          <?php if (admin_logged_in()): ?>
            <li><a href="admin_book.php"><?php echo e(t('nav_admin')); ?></a></li>
          <?php endif; ?>
          <li><a href="lang.php?lang=am">አማ</a></li>
          <li><a href="lang.php?lang=en">EN</a></li>
        </ul>
      </div>
    </div>
  </nav>
  <?php if (isset($title) && $title === 'Index'): ?>
    <div class="jumbotron">
      <div class="container"><br><br><br>
        <h1><?php echo e(t('hero_title')); ?></h1>
        <p class="lead"><?php echo e(t('hero_lead')); ?></p>
      </div>
    </div>
  <?php endif; ?>

  <div class="container" id="main">
    <?php if ($msg = flash_get('success')): ?>
      <div class="alert alert-success"><?php echo e($msg); ?></div>
    <?php endif; ?>
    <?php if ($msg = flash_get('error')): ?>
      <div class="alert alert-danger"><?php echo e($msg); ?></div>
    <?php endif; ?>
