<?php

declare(strict_types=1);

namespace Baraja\Shop\Customer;


use Baraja\Plugin\BasePlugin;
use Baraja\Plugin\SimpleComponent\Button;
use Baraja\Shop\Customer\Entity\Customer;
use Baraja\Shop\Customer\Entity\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

final class CustomerPlugin extends BasePlugin
{
	private CustomerRepository $customerRepository;


	public function __construct(EntityManagerInterface $entityManager)
	{
		$customerRepository = $entityManager->getRepository(Customer::class);
		assert($customerRepository instanceof CustomerRepository);
		$this->customerRepository = $customerRepository;
	}


	public function getName(): string
	{
		return 'Customers';
	}


	public function actionDetail(int $id): void
	{
		try {
			$customer = $this->customerRepository->getById($id);
		} catch (NoResultException | NonUniqueResultException) {
			$this->error(sprintf('Customer "%d" does not exist.', $id));
		}

		$this->setTitle(sprintf('(%d) %s', $customer->getId(), $customer->getName()));
		$this->addButton(
			new Button(
				variant: Button::VARIANT_SECONDARY,
				label: 'Change password',
				action: Button::ACTION_MODAL,
				target: 'modal-change-password',
			),
		);
	}
}
