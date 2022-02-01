<?php

declare(strict_types=1);

namespace Baraja\Shop\Customer;


use Baraja\EcommerceStandard\DTO\CustomerInterface;
use Baraja\Shop\Customer\Entity\Customer;
use Baraja\Shop\Customer\Entity\CustomerResetPasswordRequest;
use Baraja\Shop\Customer\Entity\CustomerResetPasswordRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

final class CustomerResetPasswordManager
{
	private CustomerResetPasswordRequestRepository $resetPasswordRequestRepository;


	public function __construct(
		private EntityManagerInterface $entityManager,
	) {
		$resetPasswordRequestRepository = $entityManager->getRepository(CustomerResetPasswordRequest::class);
		assert($resetPasswordRequestRepository instanceof CustomerResetPasswordRequestRepository);
		$this->resetPasswordRequestRepository = $resetPasswordRequestRepository;
	}


	public function getRequestByHash(string $hash): CustomerResetPasswordRequest
	{
		try {
			$request = $this->resetPasswordRequestRepository->getByHash($hash);
		} catch (NoResultException | NonUniqueResultException) {
			throw new \InvalidArgumentException('There is no password reset token. Please request a new link.');
		}
		if ($request->isExpired()) {
			throw new \InvalidArgumentException('The link has expired.');
		}

		return $request;
	}


	public function createNewRequest(CustomerInterface $customer): CustomerResetPasswordRequest
	{
		assert($customer instanceof Customer);
		$request = new CustomerResetPasswordRequest($customer);
		$this->entityManager->persist($request);
		$this->entityManager->flush();

		return $request;
	}


	public function setNewPassword(CustomerResetPasswordRequest $request, string $password): void
	{
		$request->setExpired();
		$request->getCustomer()->setPassword($password);
		$this->entityManager->flush();
	}
}
