<?php

declare(strict_types=1);

namespace Baraja\Shop\Customer\Entity;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

final class CustomerRepository extends EntityRepository
{
	/**
	 * @throws NoResultException|NonUniqueResultException
	 */
	public function getById(int $id): Customer
	{
		$customer = $this->createQueryBuilder('customer')
			->where('customer.id = :id')
			->setParameter('id', $id)
			->setMaxResults(1)
			->getQuery()
			->getSingleResult();
		assert($customer instanceof Customer);

		return $customer;
	}


	/**
	 * @throws NoResultException|NonUniqueResultException
	 */
	public function getByEmail(string $email): Customer
	{
		$customer = $this->createQueryBuilder('customer')
			->where('customer.email = :email')
			->setParameter('email', $email)
			->setMaxResults(1)
			->getQuery()
			->getSingleResult();
		assert($customer instanceof Customer);

		return $customer;
	}


	/**
	 * @return array<int, array{
	 *    id: int,
	 *    firstName: string,
	 *    lastName: string,
	 *    email: string|null,
	 *    phone: string|null,
	 *    premium: bool,
	 *    ban: bool,
	 *    insertedDate: \DateTimeImmutable
	 * }>
	 */
	public function getFeed(?string $query = null, int $limit = 128): array
	{
		$selector = $this->createQueryBuilder('customer')
			->select('PARTIAL customer.{id, firstName, lastName, email, phone, premium, ban, insertedDate}')
			->orderBy('customer.premium', 'DESC')
			->addOrderBy('customer.insertedDate', 'DESC')
			->setMaxResults($limit);

		if ($query !== null) {
			$query = trim((string) preg_replace('/\s+/', ' ', $query));
			foreach (explode(' ', $query) as $key => $queryPart) {
				$param = sprintf('query_%s', $key);
				$rules = array_map(
					static fn (string $col): string => sprintf('customer.%s LIKE :%s', $col, $param),
					['firstName', 'lastName', 'email', 'phone'],
				);
				$selector->andWhere(implode(' OR ', $rules));
				$selector->setParameter($param, sprintf('%%%s%%', $queryPart));
			}
		}

		/** @phpstan-ignore-next-line */
		return $selector->getQuery()->getArrayResult();
	}
}
