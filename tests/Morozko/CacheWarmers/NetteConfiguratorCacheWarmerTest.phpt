<?php

declare(strict_types = 1);

namespace OopsTests\Morozko\CacheWarmers;

use Nette\Configurator;
use Oops\Morozko\CacheWarmers\NetteConfiguratorCacheWarmer\ConfiguratorFactoryInterface;
use Oops\Morozko\CacheWarmers\NetteConfiguratorCacheWarmer;
use Tester\Environment;
use Tester\TestCase;


require_once __DIR__ . '/../../bootstrap.php';


/**
 * @testCase
 */
final class NetteConfiguratorCacheWarmerTest extends TestCase
{

	public function testCacheWarmer(): void
	{
		$configuratorMock = \Mockery::mock(Configurator::class);
		$configuratorMock->shouldReceive('addParameters')
			->withArgs([[
				'consoleMode' => FALSE,
				'debugMode' => FALSE,
				'productionMode' => TRUE,
			]])
			->once();

		$configuratorMock->shouldReceive('createContainer')
			->withArgs([])
			->once();

		$configuratorFactory = new class($configuratorMock) implements ConfiguratorFactoryInterface {
			/** @var Configurator */
			private $configurator;

			public function __construct(Configurator $configurator)
			{
				$this->configurator = $configurator;
			}

			public function create(): Configurator
			{
				return $this->configurator;
			}
		};

		$cacheWarmer = new NetteConfiguratorCacheWarmer($configuratorFactory);
		$cacheWarmer->warmup();

		Environment::$checkAssertions = FALSE;
		\Mockery::close();
	}

}


(new NetteConfiguratorCacheWarmerTest())->run();
