<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';

unset($_SESSION['admin_id'], $_SESSION['admin_name']);
session_regenerate_id(true);
flash_set('success', 'Admin signed out');
redirect_local('index.php');
