<?php

declare(strict_types=1);

function total_price(array $cart): float
{
	$price = 0.0;
	foreach ($cart as $isbn => $qty) {
		$bookprice = getbookprice((string) $isbn);
		$qty = (int) $qty;
		if ($bookprice !== null && $qty > 0) {
			$price += $bookprice * $qty;
		}
	}
	return $price;
}

function total_items(array $cart): int
{
	$items = 0;
	foreach ($cart as $qty) {
		$qty = (int) $qty;
		if ($qty > 0) {
			$items += $qty;
		}
	}
	return $items;
}

function normalize_cart_quantities(array $input): array
{
	$cart = [];
	foreach ($input as $isbn => $qty) {
		$qty = (int) $qty;
		if ($qty > 0) {
			$cart[(string) $isbn] = $qty;
		}
	}
	return $cart;
}
