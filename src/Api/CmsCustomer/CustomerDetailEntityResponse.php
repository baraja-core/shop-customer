<?php

declare(strict_types=1);

namespace Baraja\Shop\Customer\Api\CmsCustomer;


final class CustomerDetailEntityResponse
{
	public int $id;

	public string $email;

	public string $firstName;

	public string $lastName;

	public ?string $phone;

	public bool $newsletter;

	public ?string $locale;

	public ?string $note;

	public bool $premium;

	public bool $ban;

	public \DateTimeInterface $insertedDate;

	public float $defaultOrderSale;

	/** @var array<int, array{id: int, number: string, price: string, date: \DateTimeImmutable}> */
	public array $orders;
}
