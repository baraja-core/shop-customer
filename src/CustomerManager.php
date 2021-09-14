<?php

declare(strict_types=1);

namespace Baraja\Shop\Customer;


use Baraja\Doctrine\EntityManager;
use Baraja\Shop\Customer\Entity\Customer;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

final class CustomerManager
{
	public function __construct(
		private EntityManager $entityManager,
	) {
	}


	public function createCustomer(string $email, string $firstName, string $lastName): Customer
	{
		try {
			$this->getByEmail($email);
			throw new \InvalidArgumentException('Customer "' . $email . '" already exist.');
		} catch (NoResultException | NonUniqueResultException) {
			// Silence is golden.
		}

		$customer = new Customer($email, $firstName, $lastName);
		$this->entityManager->persist($customer);
		$this->entityManager->flush();

		return $customer;
	}


	/**
	 * @throws NoResultException|NonUniqueResultException
	 */
	public function getByEmail(string $email): Customer
	{
		return $this->entityManager->getRepository(Customer::class)
			->createQueryBuilder('customer')
			->where('customer.email = :email')
			->setParameter('email', $email)
			->setMaxResults(1)
			->getQuery()
			->getSingleResult();
	}


	/**
	 * @throws NoResultException|NonUniqueResultException
	 */
	public function getById(int $id): Customer
	{
		return $this->entityManager->getRepository(Customer::class)
			->createQueryBuilder('c')
			->where('c.id = :id')
			->setParameter('id', $id)
			->setMaxResults(1)
			->getQuery()
			->getSingleResult();
	}
}
