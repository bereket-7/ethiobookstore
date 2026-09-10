<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';

unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email']);
flash_set('success', 'Logged out');
redirect_local('index.php');
