<?php

declare(strict_types = 1);

namespace OopsTests\Morozko\CacheWarmers;

use Oops\Morozko\CacheWarmers\NetteConfiguratorCacheWarmer;
use Oops\Morozko\CacheWarmupFailedException;
use Tester\Assert;
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
		$lockFile = \dirname(\TEMP_DIR) . '/lock-' . self::LOCK;
		\flock($lock = \fopen($lockFile, 'w'), \LOCK_EX);

		@\unlink(__DIR__ . '/fixtures/successfulWwwDir/hit'); // @ - file may not exist
		Assert::false(\file_exists(__DIR__ . '/fixtures/successfulWwwDir/hit'));

		$cacheWarmer = new NetteConfiguratorCacheWarmer(__DIR__ . '/fixtures/successfulWwwDir');
		$cacheWarmer->warmup();

		Assert::true(\file_exists(__DIR__ . '/fixtures/successfulWwwDir/hit'));
		Assert::same('hit!', \file_get_contents(__DIR__ . '/fixtures/successfulWwwDir/hit'));

		\flock($lock, \LOCK_UN);
	}


	public function testFailingWwwDir(): void
	{
		$lockFile = \dirname(\TEMP_DIR) . '/lock-' . self::LOCK;
		\flock($lock = \fopen($lockFile, 'w'), \LOCK_EX);

		$cacheWarmer = new NetteConfiguratorCacheWarmer(__DIR__ . '/fixtures/failingWwwDir');
		Assert::throws(function () use ($cacheWarmer): void {
			$cacheWarmer->warmup();
		}, CacheWarmupFailedException::class, '');

		\flock($lock, \LOCK_UN);
	}

}


(new NetteConfiguratorCacheWarmerTest())->run();
