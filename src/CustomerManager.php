<?php

declare(strict_types=1);

namespace Baraja\Shop\Customer;


use Baraja\Localization\Localization;
use Baraja\Shop\Customer\Entity\Customer;
use Baraja\Shop\Customer\Entity\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

final class CustomerManager
{
	private CustomerRepository $customerRepository;


	public function __construct(
		private EntityManagerInterface $entityManager,
		private Localization $localization,
	) {
		$customerRepository = $entityManager->getRepository(Customer::class);
		assert($customerRepository instanceof CustomerRepository);
		$this->customerRepository = $customerRepository;
	}


	public function createCustomer(string $email, string $firstName, string $lastName): Customer
	{
		try {
			$this->customerRepository->getByEmail($email);
			throw new \InvalidArgumentException(sprintf('Customer "%s" already exist.', $email));
		} catch (NoResultException | NonUniqueResultException) {
			// Silence is golden.
		}

		$customer = new Customer($email, $firstName, $lastName);
		if (PHP_SAPI === 'cli') {
			$customer->setLocale($this->localization->getDefaultLocale());
		} else {
			$customer->setLocale($this->localization->getLocale());
		}
		$this->entityManager->persist($customer);
		$this->entityManager->flush();

		return $customer;
	}


	/**
	 * @throws NoResultException|NonUniqueResultException
	 * @deprecated use CustomerRepository instead.
	 */
	public function getByEmail(string $email): Customer
	{
		return $this->customerRepository->getByEmail($email);
	}


	/**
	 * @throws NoResultException|NonUniqueResultException
	 * @deprecated use CustomerRepository instead.
	 */
	public function getById(int $id): Customer
	{
		return $this->customerRepository->getById($id);
	}
}
