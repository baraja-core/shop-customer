<?php

declare(strict_types=1);

namespace Baraja\Shop\Customer;


use Baraja\Doctrine\EntityManager;
use Baraja\Localization\Localization;
use Baraja\Shop\Customer\Entity\Customer;
use Baraja\StructuredApi\BaseEndpoint;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

final class CmsCustomerEndpoint extends BaseEndpoint
{
	public function __construct(
		private EntityManager $entityManager,
		private CustomerManager $customerManager,
		private Localization $localization,
		private ?OrderLoader $orderLoader = null,
	) {
	}


	public function actionDefault(?string $query = null): void
	{
		$selector = $this->entityManager->getRepository(Customer::class)
			->createQueryBuilder('customer')
			->select('PARTIAL customer.{id, firstName, lastName, email, phone, premium, ban, insertedDate}')
			->orderBy('customer.premium', 'DESC')
			->addOrderBy('customer.insertedDate', 'DESC')
			->setMaxResults(128);

		if ($query !== null) {
			$selector->andWhere(
				'customer.firstName LIKE :query OR customer.lastName LIKE :query OR customer.email LIKE :query OR customer.phone LIKE :query'
			)->setParameter('query', '%' . $query . '%');
		}

		$this->sendJson(
			[
				'items' => $selector->getQuery()->getArrayResult(),
			]
		);
	}


	public function actionDetail(int $id): void
	{
		$customer = $this->customerManager->getById($id);
		$this->sendJson(
			[
				'customer' => [
					'id' => $customer->getId(),
					'email' => $customer->getEmail(),
					'firstName' => $customer->getFirstName(),
					'lastName' => $customer->getLastName(),
					'phone' => $customer->getPhone(),
					'newsletter' => $customer->isNewsletter(),
					'locale' => $customer->getLocale(),
					'note' => $customer->getNote(),
					'premium' => $customer->isPremium(),
					'ban' => $customer->isBan(),
					'insertedDate' => $customer->getInsertedDate(),
					'defaultOrderSale' => $customer->getDefaultOrderSale(),
					'orders' => $this->orderLoader !== null ? $this->orderLoader->getOrders($id) : [],
				],
				'locales' => $this->formatBootstrapSelectArray(
					[null => '--'] + array_combine(
						$this->localization->getAvailableLocales(),
						$this->localization->getAvailableLocales()
					)
				),
			]
		);
	}


	public function postCreateCustomer(string $email, string $firstName, string $lastName): void
	{
		try {
			$this->customerManager->createCustomer($email, $firstName, $lastName);
		} catch (\InvalidArgumentException $e) {
			$this->sendError($e->getMessage());
		}
		$this->flashMessage('Customer has been created.', 'success');
		$this->sendOk();
	}


	public function postSave(
		int $id,
		string $email,
		string $firstName,
		string $lastName,
		string $phone,
		?string $locale,
		?string $note,
		bool $premium,
		bool $ban,
		float $defaultOrderSale,
	): void {
		try {
			$customer = $this->customerManager->getById($id);
		} catch (NoResultException | NonUniqueResultException) {
			$this->sendError('Customer "' . $id . '" does not exist.');
		}
		$customer->setEmail($email);
		$customer->setFirstName($firstName);
		$customer->setLastName($lastName);
		$customer->setPhone($phone);
		$customer->setLocale($locale);
		$customer->setNote($note);
		$customer->setPremium($premium);
		$customer->setBan($ban);
		$customer->setDefaultOrderSale($defaultOrderSale);

		$this->entityManager->flush();
		$this->flashMessage('Customer information has been saved.', 'success');
		$this->sendOk();
	}


	public function postSavePassword(int $id, string $password): void
	{
		try {
			$customer = $this->customerManager->getById($id);
		} catch (NoResultException | NonUniqueResultException) {
			$this->sendError('Customer "' . $id . '" does not exist.');
		}
		$customer->setPassword($password);
		$this->entityManager->flush();
		$this->flashMessage('Customer password has been changed.', 'success');
		$this->sendOk();
	}
}
