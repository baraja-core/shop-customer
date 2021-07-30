<?php

declare(strict_types=1);

namespace Baraja\Shop\Customer;


use Baraja\Doctrine\EntityManager;
use Baraja\Shop\Customer\Entity\Customer;
use Baraja\StructuredApi\BaseEndpoint;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

final class CmsCustomerEndpoint extends BaseEndpoint
{
	public function __construct(
		private EntityManager $entityManager,
		private ?OrderLoader $orderLoader = null,
	) {
	}


	public function actionDefault(?string $query = null): void
	{
		$selector = $this->entityManager->getRepository(Customer::class)
			->createQueryBuilder('customer')
			->select('PARTIAL customer.{id, firstName, lastName, email, phone}')
			->orderBy('customer.insertedDate', 'DESC')
			->setMaxResults(128);

		if ($query !== null) {
			$selector->andWhere(
				'customer.firstName LIKE :query OR customer.lastName LIKE :query OR customer.email LIKE :query OR customer.phone LIKE :query'
			)
				->setParameter('query', '%' . $query . '%');
		}

		$this->sendJson(
			[
				'items' => $selector->getQuery()->getArrayResult(),
			]
		);
	}


	public function actionDetail(int $id): void
	{
		$customer = $this->getCustomer($id);

		$this->sendJson(
			[
				'id' => $customer->getId(),
				'email' => $customer->getEmail(),
				'firstName' => $customer->getFirstName(),
				'lastName' => $customer->getLastName(),
				'phone' => $customer->getPhone(),
				'newsletter' => $customer->isNewsletter(),
				'insertedDate' => $customer->getInsertedDate(),
				'defaultOrderSale' => $customer->getDefaultOrderSale(),
				'orders' => $this->orderLoader !== null ? $this->orderLoader->getOrders($customer->getId()) : [],
			]
		);
	}


	public function postCreateCustomer(string $email, string $firstName, string $lastName): void
	{
		try {
			$this->entityManager->getRepository(Customer::class)
				->createQueryBuilder('c')
				->where('c.email = :email')
				->setParameter('email', $email)
				->setMaxResults(1)
				->getQuery()
				->getSingleResult();

			$this->sendError('Customer "' . $email . '" already exist.');
		} catch (NoResultException | NonUniqueResultException) {
			// Silence is golden.
		}

		$customer = new Customer($email, $firstName, $lastName);
		$this->entityManager->persist($customer);
		$this->entityManager->flush();
		$this->flashMessage('Customer has been created.', 'success');
		$this->sendOk();
	}


	public function postSave(
		int $id,
		string $email,
		string $firstName,
		string $lastName,
		string $phone,
		float $defaultOrderSale,
	): void {
		try {
			$customer = $this->getCustomer($id);
		} catch (NoResultException | NonUniqueResultException) {
			$this->sendError('Customer "' . $id . '" does not exist.');
		}
		$customer->setEmail($email);
		$customer->setFirstName($firstName);
		$customer->setLastName($lastName);
		$customer->setPhone($phone);
		$customer->setDefaultOrderSale($defaultOrderSale);

		$this->entityManager->flush();
		$this->flashMessage('Customer information has been saved.', 'success');
		$this->sendOk();
	}


	/**
	 * @throws NoResultException|NonUniqueResultException
	 */
	private function getCustomer(int $id): Customer
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
