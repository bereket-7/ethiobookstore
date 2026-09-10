<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';

$lang = $_GET['lang'] ?? 'am';
if (!in_array($lang, ['am', 'en'], true)) {
	$lang = 'am';
}
$_SESSION['lang'] = $lang;

$referer = $_SERVER['HTTP_REFERER'] ?? '';
$path = parse_url($referer, PHP_URL_PATH) ?: '';
$script = basename($path);
if ($script === '' || $script === 'lang.php') {
	$script = 'index.php';
}
redirect_local($script);
