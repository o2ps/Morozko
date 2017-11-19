<?php

declare(strict_types = 1);

namespace OopsTests\Morozko;

use Oops\Morozko\CacheWarmer;
use Oops\Morozko\CacheWarmupFailedException;


final class SuccessfulCacheWarmer implements CacheWarmer {
	public function warmup(): void
	{
	}
}


final class FailingCacheWarmer implements CacheWarmer {
	public function warmup(): void
	{
		throw new CacheWarmupFailedException('Cache warmup failed!');
	}
}
