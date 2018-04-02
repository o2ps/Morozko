<?php

declare(strict_types = 1);

namespace Oops\Morozko\DI;

use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;
use Oops\Morozko\CacheWarmer;
use Oops\Morozko\CacheWarmers\NetteConfiguratorCacheWarmer\ConfiguratorFactoryInterface;
use Oops\Morozko\CacheWarmers\NetteConfiguratorCacheWarmer;
use Oops\Morozko\Configuration;
use Oops\Morozko\Console\WarmupCommand;
use Symfony\Component\Console\Application;


final class MorozkoExtension extends CompilerExtension
{

	private $defaults = [
		'configuratorFactory' => NULL,
	];


	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();
		$config = $this->validateConfig($this->defaults);

		$configuration = $builder->addDefinition($this->prefix('configuration'))
			->setType(Configuration::class)
			->setFactory(Configuration::class)
			->setAutowired(FALSE);

		$builder->addDefinition($this->prefix('console.command'))
			->setType(WarmupCommand::class)
			->setFactory(WarmupCommand::class, [$configuration])
			->setAutowired(FALSE);


		// DI container cache warmer
		if (empty($config['configuratorFactory'])) {
			throw ConfigurationException::missingConfiguratorFactory($this->name);
		}

		$configuratorFactoryServiceName = $this->prefix('configuratorFactory');
		$configuratorFactoryDefinition = $config['configuratorFactory'];
		Compiler::loadDefinitions($builder, [$configuratorFactoryServiceName => $configuratorFactoryDefinition]);
		$builder->getDefinition($configuratorFactoryServiceName)->setType(ConfiguratorFactoryInterface::class);

		$builder->addDefinition($this->prefix('warmers.di'))
			->setType(NetteConfiguratorCacheWarmer::class)
			->setFactory(NetteConfiguratorCacheWarmer::class, ['@' . $configuratorFactoryServiceName])
			->setAutowired(FALSE);
	}


	public function beforeCompile(): void
	{
		$builder = $this->getContainerBuilder();

		// check that Symfony/Console extension is registered
		$consoleApplicationServiceName = $builder->getByType(Application::class);
		if ($consoleApplicationServiceName === NULL) {
			throw new \LogicException(
				'No service of type Symfony\\Component\\Console\\Application found. ' .
				'You must register a Symfony/Console integration extension to use Morozko.'
			);
		}

		// register Console command
		$command = $builder->getDefinition($this->prefix('console.command'));
		$consoleApplicationDefinition = $builder->getDefinition($consoleApplicationServiceName);
		$consoleApplicationDefinition->addSetup('add', [$command]);

		// register cache warmers
		$configuration = $builder->getDefinition($this->prefix('configuration'));
		foreach ($builder->findByType(CacheWarmer::class) as $warmerDefinition) {
			$configuration->addSetup('addCacheWarmer', [$warmerDefinition]);
		}
	}

}
