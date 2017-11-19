<?php

declare(strict_types = 1);

namespace OopsTests\Morozko\CacheWarmers;

use Oops\Morozko\CacheWarmers\NetteConfiguratorCacheWarmer;
use Oops\Morozko\CacheWarmupFailedException;
use Tester\Assert;
use Tester\Environment;
use Tester\TestCase;


require_once __DIR__ . '/../../bootstrap.php';


/**
 * @testCase
 */
final class NetteConfiguratorCacheWarmerTest extends TestCase
{

	private const LOCK = 'NetteConfiguratorCacheWarmerTest';


	public function testSuccessfulWwwDir(): void
	{
		Environment::lock(self::LOCK, \dirname(\TEMP_DIR));

		@\unlink(__DIR__ . '/fixtures/successfulWwwDir/hit'); // @ - file may not exist
		Assert::false(\file_exists(__DIR__ . '/fixtures/successfulWwwDir/hit'));

		$cacheWarmer = new NetteConfiguratorCacheWarmer(__DIR__ . '/fixtures/successfulWwwDir');
		$cacheWarmer->warmup();

		Assert::true(\file_exists(__DIR__ . '/fixtures/successfulWwwDir/hit'));
		Assert::same('hit!', \file_get_contents(__DIR__ . '/fixtures/successfulWwwDir/hit'));
	}


	public function testFailingWwwDir(): void
	{
		Environment::lock(self::LOCK, \dirname(\TEMP_DIR));

		$cacheWarmer = new NetteConfiguratorCacheWarmer(__DIR__ . '/fixtures/failingWwwDir');
		Assert::throws(function () use ($cacheWarmer): void {
			$cacheWarmer->warmup();
		}, CacheWarmupFailedException::class, '');
	}

}


(new NetteConfiguratorCacheWarmerTest())->run();
