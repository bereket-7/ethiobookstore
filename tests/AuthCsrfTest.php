<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class AuthCsrfTest extends TestCase
{
	protected function setUp(): void
	{
		$_SESSION = [];
	}

	public function testCsrfRoundTrip(): void
	{
		$token = csrf_token();
		$this->assertNotSame('', $token);
		$this->assertTrue(csrf_verify($token));
		$this->assertFalse(csrf_verify('bad-token'));
	}

	public function testAdminGateDefaultsFalse(): void
	{
		$this->assertFalse(admin_logged_in());
		$this->assertFalse(customer_logged_in());
	}

	public function testLoginRateLimit(): void
	{
		$this->assertFalse(login_rate_limited('testbucket', 3, 900));
		login_rate_hit('testbucket');
		login_rate_hit('testbucket');
		login_rate_hit('testbucket');
		$this->assertTrue(login_rate_limited('testbucket', 3, 900));
		login_rate_clear('testbucket');
		$this->assertFalse(login_rate_limited('testbucket', 3, 900));
	}
}
