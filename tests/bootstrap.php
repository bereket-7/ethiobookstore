<?php

declare(strict_types=1);

// CLI-safe session for tests
if (session_status() !== PHP_SESSION_ACTIVE) {
	@session_start();
}

require_once dirname(__DIR__) . '/bookstore/lib/bootstrap.php';
