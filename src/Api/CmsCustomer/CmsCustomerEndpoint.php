<?php

declare(strict_types=1);

namespace Baraja\Shop\Customer\Api\CmsCustomer;


use Baraja\Localization\Localization;
use Baraja\Shop\Customer\CustomerManager;
use Baraja\Shop\Customer\Entity\Customer;
use Baraja\Shop\Customer\Entity\CustomerRepository;
use Baraja\Shop\Customer\OrderLoader;
use Baraja\StructuredApi\BaseEndpoint;
use Baraja\StructuredApi\Response\Status\ErrorResponse;
use Baraja\StructuredApi\Response\Status\OkResponse;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

final class CmsCustomerEndpoint extends BaseEndpoint
{
	private CustomerRepository $customerRepository;


	public function __construct(
		private EntityManagerInterface $entityManager,
		private CustomerManager $customerManager,
		private Localization $localization,
		private ?OrderLoader $orderLoader = null,
	) {
		$customerRepository = $entityManager->getRepository(Customer::class);
		assert($customerRepository instanceof CustomerRepository);
		$this->customerRepository = $customerRepository;
	}


	public function actionDefault(?string $query = null): CmsCustomerResponse
	{
		return new CmsCustomerResponse(
			items: $this->customerRepository->getFeed($query),
		);
	}


	public function actionDetail(int $id): CustomerDetailResponse
	{
		$customer = $this->customerRepository->getById($id);

		$entity = new CustomerDetailEntityResponse;
		$entity->id = $customer->getId();
		$entity->email = $customer->getEmail();
		$entity->firstName = $customer->getFirstName();
		$entity->lastName = $customer->getLastName();
		$entity->phone = $customer->getPhone();
		$entity->newsletter = $customer->isNewsletter();
		$entity->locale = $customer->getLocale();
		$entity->note = $customer->getNote();
		$entity->premium = $customer->isPremium();
		$entity->ban = $customer->isBan();
		$entity->insertedDate = $customer->getInsertedDate();
		$entity->defaultOrderSale = $customer->getDefaultOrderSale();
		$entity->orders = $this->orderLoader !== null ? $this->orderLoader->getOrders($id) : [];

		return new CustomerDetailResponse(
			customer: $entity,
			locales: $this->formatBootstrapSelectArray(
				[null => '--'] + array_combine(
					$this->localization->getAvailableLocales(),
					$this->localization->getAvailableLocales(),
				),
			),
		);
	}


	public function postCreateCustomer(string $email, string $firstName, string $lastName): OkResponse
	{
		try {
			$this->customerManager->createCustomer($email, $firstName, $lastName);
		} catch (\InvalidArgumentException $e) {
			ErrorResponse::invoke($e->getMessage());
		}
		$this->flashMessage('Customer has been created.', 'success');

		return new OkResponse;
	}


	public function postSave(
		int $id,
		string $email,
		string $firstName,
		string $lastName,
		?string $phone,
		?string $locale,
		?string $note,
		bool $premium,
		bool $ban,
		float $defaultOrderSale,
	): OkResponse {
		try {
			$customer = $this->customerRepository->getById($id);
		} catch (NoResultException | NonUniqueResultException) {
			$this->sendError(sprintf('Customer "%d" does not exist.', $id));
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

		return new OkResponse;
	}


	public function postSavePassword(int $id, string $password): OkResponse
	{
		try {
			$customer = $this->customerRepository->getById($id);
		} catch (NoResultException | NonUniqueResultException) {
			$this->sendError(sprintf('Customer "%d" does not exist.', $id));
		}
		$customer->setPassword($password);
		$this->entityManager->flush();
		$this->flashMessage('Customer password has been changed.', 'success');

		return new OkResponse;
	}
}
