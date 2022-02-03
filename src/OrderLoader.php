<?php

declare(strict_types=1);

namespace Baraja\Shop\Customer;


interface OrderLoader
{
	/**
	 * @return array<int, array{id: int, number: string, price: string, date: \DateTimeImmutable}>
	 */
	public function getOrders(int $customerId): array;
}
