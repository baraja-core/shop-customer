<?php

declare(strict_types=1);

namespace Baraja\Shop\Customer\Entity;


use Doctrine\ORM\Mapping as ORM;
use Nette\Utils\Random;

#[ORM\Entity(repositoryClass: CustomerResetPasswordRequestRepository::class)]
#[ORM\Table(name: 'shop__customer_reset_password_request')]
class CustomerResetPasswordRequest
{
	#[ORM\Id]
	#[ORM\Column(type: 'integer', unique: true, options: ['unsigned' => true])]
	#[ORM\GeneratedValue]
	protected int $id;

	#[ORM\ManyToOne(targetEntity: Customer::class)]
	private Customer $customer;

	#[ORM\Column(type: 'string', length: 32, unique: true)]
	private string $hash;

	#[ORM\Column(type: 'boolean')]
	private bool $expired = false;

	#[ORM\Column(type: 'datetime_immutable')]
	private \DateTimeInterface $expiration;


	public function __construct(Customer $customer)
	{
		$this->customer = $customer;
		$this->hash = md5(Random::generate(32));
		$this->expiration = new \DateTimeImmutable('now + 2 hours');
	}


	public function getId(): int
	{
		return $this->id;
	}


	public function getCustomer(): Customer
	{
		return $this->customer;
	}


	public function getHash(): string
	{
		return $this->hash;
	}


	public function isExpired(): bool
	{
		if ($this->expired === true) {
			return true;
		}

		return time() > $this->expiration->getTimestamp();
	}


	public function setExpired(): void
	{
		$this->expired = true;
	}
}
