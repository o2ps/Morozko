<?php

declare(strict_types = 1);

namespace OopsTests\Morozko\DI;

use Nette\Configurator;
use Oops\Morozko\CacheWarmers\NetteConfiguratorCacheWarmer\ConfiguratorFactoryInterface;


final class FakeConfiguratorFactory implements ConfiguratorFactoryInterface
{

	public function create(): Configurator
	{
		return new Configurator();
	}

}
