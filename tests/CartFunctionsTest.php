<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class CartFunctionsTest extends TestCase
{
	public function testNormalizeQuantities(): void
	{
		$out = normalize_cart_quantities([
			'978-1' => '2',
			'978-2' => '0',
			'978-3' => '-1',
			'978-4' => '3',
		]);
		$this->assertSame(['978-1' => 2, '978-4' => 3], $out);
		$this->assertSame(5, total_items($out));
	}
}
