<?php

declare(strict_types=1);

namespace Baraja\Shop\Customer;


use Baraja\Doctrine\EntityManager;
use Baraja\Plugin\BasePlugin;
use Baraja\Plugin\SimpleComponent\Button;
use Baraja\Shop\Customer\Entity\Customer;

final class CustomerPlugin extends BasePlugin
{
	public function __construct(
		private EntityManager $entityManager,
	) {
	}


	public function getName(): string
	{
		return 'Customers';
	}


	public function actionDetail(int $id): void
	{
		/** @var Customer|null $customer */
		$customer = $this->entityManager->getRepository(Customer::class)->find($id);

		if ($customer === null) {
			$this->error();
		}

		$this->setTitle('(' . $customer->getId() . ') ' . $customer->getName());
		$this->addButton(
			new Button(
				variant: Button::VARIANT_SECONDARY,
				label: 'Change password',
				action: Button::ACTION_MODAL,
				target: 'modal-change-password',
			)
		);
	}
}
