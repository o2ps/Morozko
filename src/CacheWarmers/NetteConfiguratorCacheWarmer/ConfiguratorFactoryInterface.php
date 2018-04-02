<?php declare(strict_types = 1);

namespace Oops\Morozko\CacheWarmers\NetteConfiguratorCacheWarmer;

use Nette\Configurator;


interface ConfiguratorFactoryInterface
{

	public function create(): Configurator;

}
