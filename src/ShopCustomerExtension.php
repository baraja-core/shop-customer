<?php

declare(strict_types=1);

namespace Baraja\Shop\Customer;


use Baraja\Doctrine\ORM\DI\OrmAnnotationsExtension;
use Baraja\Plugin\Component\VueComponent;
use Baraja\Plugin\PluginManager;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\ServiceDefinition;

final class ShopCustomerExtension extends CompilerExtension
{
	/**
	 * @return string[]
	 */
	public static function mustBeDefinedBefore(): array
	{
		return [PluginComponentExtension::class];
	}


	public function beforeCompile(): void
	{
		$builder = $this->getContainerBuilder();
		OrmAnnotationsExtension::addAnnotationPathToManager($builder, 'Baraja\Shop\Customer\Entity', __DIR__ . '/Entity');

		$builder->addDefinition($this->prefix('customerManager'))
			->setFactory(CustomerManager::class)
			->setAutowired(CustomerManager::class);

		/** @var ServiceDefinition $pluginManager */
		$pluginManager = $this->getContainerBuilder()->getDefinitionByType(PluginManager::class);
		$pluginManager->addSetup('?->addComponent(?)', ['@self', [
			'key' => 'customerDefault',
			'name' => 'cms-customer-default',
			'implements' => CustomerPlugin::class,
			'componentClass' => VueComponent::class,
			'view' => 'default',
			'source' => __DIR__ . '/../template/default.js',
			'position' => 100,
			'tab' => 'Customer',
			'params' => [],
		]]);
		$pluginManager->addSetup('?->addComponent(?)', ['@self', [
			'key' => 'customerOverview',
			'name' => 'cms-customer-overview',
			'implements' => CustomerPlugin::class,
			'componentClass' => VueComponent::class,
			'view' => 'detail',
			'source' => __DIR__ . '/../template/overview.js',
			'position' => 100,
			'tab' => 'Overview',
			'params' => [
				'id',
			],
		]]);
	}
}
