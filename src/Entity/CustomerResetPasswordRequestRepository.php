<?php

declare(strict_types=1);

namespace Baraja\Shop\Customer\Entity;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

final class CustomerResetPasswordRequestRepository extends EntityRepository
{
	/**
	 * @throws NoResultException|NonUniqueResultException
	 */
	public function getByHash(string $hash): CustomerResetPasswordRequest
	{
		$request = $this->createQueryBuilder('r')
			->where('r.hash = :hash')
			->setParameter('hash', $hash)
			->setMaxResults(1)
			->getQuery()
			->getSingleResult();
		assert($request instanceof CustomerResetPasswordRequest);

		return $request;
	}
}
