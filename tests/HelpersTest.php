<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class HelpersTest extends TestCase
{
	public function testHtmlEscape(): void
	{
		$this->assertSame('&lt;script&gt;', e('<script>'));
	}

	public function testPagination(): void
	{
		$p = pagination(25, 2, 10);
		$this->assertSame(2, $p['page']);
		$this->assertSame(10, $p['offset']);
		$this->assertSame(3, $p['total_pages']);
	}

	public function testMoneyFormat(): void
	{
		$this->assertStringContainsString('20.00', money_etb(20));
	}
}
