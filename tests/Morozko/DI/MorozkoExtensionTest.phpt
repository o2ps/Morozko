<?php

declare(strict_types = 1);

namespace OopsTests\Morozko\DI;

use Nette\Configurator;
use Nette\DI\Container;
use Oops\Morozko\CacheWarmers\NetteConfiguratorCacheWarmer;
use Oops\Morozko\Configuration;
use OopsTests\Morozko\SuccessfulCacheWarmer;
use Symfony\Component\Console\Application;
use Tester\Assert;
use Tester\TestCase;


require_once __DIR__ . '/../../bootstrap.php';


/**
 * @testCase
 */
final class MorozkoExtensionTest extends TestCase
{

	public function testExtension(): void
	{
		$container = $this->createContainer('default');

		/** @var Configuration $configuration */
		$configuration = $container->getService('morozko.configuration');
		Assert::type(Configuration::class, $configuration);

		$warmers = $configuration->getCacheWarmers();
		Assert::count(2, $warmers);
		Assert::type(NetteConfiguratorCacheWarmer::class, $warmers[0]);
		Assert::type(SuccessfulCacheWarmer::class, $warmers[1]);

		/** @var Application $consoleApplication */
		$consoleApplication = $container->getByType(Application::class);
		Assert::true($consoleApplication->has('oops:morozko:warmup'));
	}


	public function testConsoleDependency(): void
	{
		$expectedMessage = 'No service of type Symfony\\Component\\Console\\Application found. ' .
			'You must register a Symfony/Console integration extension to use Morozko.';

		Assert::throws(function (): void {
			$this->createContainer('missingConsole');
		}, \LogicException::class, $expectedMessage);
	}


	private function createContainer(string $configFile): Container
	{
		$configurator = new Configurator();
		$configurator->setTempDirectory(\TEMP_DIR);
		$configurator->addConfig(__DIR__ . '/fixtures/' . $configFile . '.neon');
		return $configurator->createContainer();
	}

}


(new MorozkoExtensionTest())->run();
