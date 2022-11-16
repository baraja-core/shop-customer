<?php

declare(strict_types=1);

namespace Baraja\Shop\Customer\Api\CmsCustomer;


final class CustomerDetailResponse
{
	/**
	 * @param array<int, array{value: int|string, text: string}> $locales
	 */
	public function __construct(
		public CustomerDetailEntityResponse $customer,
		public array $locales,
	) {
	}
}
