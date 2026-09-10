<?php

declare(strict_types=1);

if (defined('APP_BOOTSTRAPPED')) {
	return;
}

define('APP_BOOTSTRAPPED', true);
define('APP_ROOT', dirname(__DIR__));
define('PROJECT_ROOT', dirname(APP_ROOT));

require_once APP_ROOT . '/config.php';
require_once APP_ROOT . '/lib/helpers.php';
require_once APP_ROOT . '/lib/csrf.php';
require_once APP_ROOT . '/lib/auth.php';
require_once APP_ROOT . '/lib/upload.php';
require_once APP_ROOT . '/lib/mail.php';
require_once APP_ROOT . '/lib/payments/ChapaClient.php';
require_once APP_ROOT . '/functions/database_functions.php';
require_once APP_ROOT . '/functions/cart_functions.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
	session_start();
}

$langCode = $_SESSION['lang'] ?? config('app.lang', 'am');
$langFile = APP_ROOT . '/lang/' . $langCode . '.php';
if (!is_file($langFile)) {
	$langFile = APP_ROOT . '/lang/en.php';
}
/** @var array<string, string> $LANG */
$LANG = require $langFile;
$GLOBALS['LANG'] = $LANG;
