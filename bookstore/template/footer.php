      <hr>
      <footer>
        <div class="text-muted pull-left">
          &copy; <?php echo date('Y'); ?> <?php echo e(t('site_name')); ?>
        </div>
        <div class="text-muted pull-right">
          <?php if (admin_logged_in()): ?>
            <a href="admin_dashboard.php"><?php echo e(t('nav_admin')); ?></a>
          <?php endif; ?>
        </div>
      </footer>
    </div>
    <script type="text/javascript" src="./bootstrap/js/jquery-2.1.4.min.js"></script>
    <script type="text/javascript" src="./bootstrap/js/bootstrap.min.js"></script>
  </body>
</html>
