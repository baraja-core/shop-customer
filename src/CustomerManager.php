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
}
