<?php

declare(strict_types = 1);

namespace OopsTests\Morozko;

use Oops\Morozko\CacheWarmer;
use Oops\Morozko\Configuration;
use Tester\Assert;
use Tester\TestCase;


require_once __DIR__ . '/../bootstrap.php';


/**
 * @testCase
 */
final class CacheWarmersCollectionTest extends TestCase
{

	public function testCollection(): void
	{
		$collection = new Configuration();
		Assert::same([], $collection->getCacheWarmers());

		$warmer = new class implements CacheWarmer {
			public function warmup(): void
			{
			}
		};

		$collection->addCacheWarmer($warmer);
		Assert::same([$warmer], $collection->getCacheWarmers());
	}

}


(new CacheWarmersCollectionTest())->run();
