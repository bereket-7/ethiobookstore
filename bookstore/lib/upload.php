<?php

declare(strict_types=1);

/**
 * @return array{ok:bool, filename?:string, error?:string}
 */
function store_book_image(array $file): array
{
	if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
		return ['ok' => false, 'error' => 'no_file'];
	}
	if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
		return ['ok' => false, 'error' => 'upload_failed'];
	}

	$maxBytes = 2 * 1024 * 1024;
	if (($file['size'] ?? 0) > $maxBytes) {
		return ['ok' => false, 'error' => 'too_large'];
	}

	$finfo = new finfo(FILEINFO_MIME_TYPE);
	$mime = $finfo->file($file['tmp_name']);
	$allowed = [
		'image/jpeg' => 'jpg',
		'image/png' => 'png',
		'image/webp' => 'webp',
	];
	if (!isset($allowed[$mime])) {
		return ['ok' => false, 'error' => 'invalid_type'];
	}

	$filename = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
	$destDir = APP_ROOT . '/bootstrap/img';
	if (!is_dir($destDir)) {
		mkdir($destDir, 0775, true);
	}
	$dest = $destDir . '/' . $filename;
	if (!move_uploaded_file($file['tmp_name'], $dest)) {
		return ['ok' => false, 'error' => 'move_failed'];
	}

	return ['ok' => true, 'filename' => $filename];
}
