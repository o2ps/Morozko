<?php

declare(strict_types = 1);

namespace Oops\Morozko\CacheWarmers;

use Oops\Morozko\CacheWarmer;
use Oops\Morozko\CacheWarmers\NetteConfiguratorCacheWarmer\ConfiguratorFactoryInterface;


final class NetteConfiguratorCacheWarmer implements CacheWarmer
{

	/**
	 * @var ConfiguratorFactoryInterface
	 */
	private $configuratorFactory;


	public function __construct(ConfiguratorFactoryInterface $configuratorFactory)
	{
		$this->configuratorFactory = $configuratorFactory;
	}


	public function warmup(): void
	{
		$configurator = $this->configuratorFactory->create();
		$configurator->addParameters([
			'consoleMode' => FALSE,
			'debugMode' => FALSE,
			'productionMode' => TRUE,
		]);

		$configurator->createContainer();
	}

}
