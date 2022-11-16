<?php

declare(strict_types=1);

namespace Baraja\Shop\Customer\Api\CmsCustomer;


final class CmsCustomerResponse
{
	/**
	 * @param array<int, array{
	 *    id: int,
	 *    firstName: string,
	 *    lastName: string,
	 *    email: string|null,
	 *    phone: string|null,
	 *    premium: bool,
	 *    ban: bool,
	 *    insertedDate: \DateTimeImmutable
	 * }> $items
	 */
	public function __construct(
		public array $items = [],
	) {
	}
}
