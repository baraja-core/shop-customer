<?php

declare(strict_types=1);

namespace Baraja\Shop\Customer;


use Baraja\Localization\Localization;
use Baraja\Shop\Customer\Entity\Customer;
use Baraja\Shop\Customer\Entity\CustomerRepository;
use Baraja\StructuredApi\BaseEndpoint;
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


	public function actionDefault(?string $query = null): void
	{
		$this->sendJson(
			[
				'items' => $this->customerRepository->getFeed($query),
			],
		);
	}


	public function actionDetail(int $id): void
	{
		$customer = $this->customerRepository->getById($id);
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
						$this->localization->getAvailableLocales(),
					),
				),
			],
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
		$this->sendOk();
	}


	public function postSavePassword(int $id, string $password): void
	{
		try {
			$customer = $this->customerRepository->getById($id);
		} catch (NoResultException | NonUniqueResultException) {
			$this->sendError(sprintf('Customer "%d" does not exist.', $id));
		}
		$customer->setPassword($password);
		$this->entityManager->flush();
		$this->flashMessage('Customer password has been changed.', 'success');
		$this->sendOk();
	}
}
