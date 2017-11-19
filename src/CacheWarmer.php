<?php

declare(strict_types = 1);

namespace Oops\Morozko;


interface CacheWarmer
{

	/**
	 * @throws CacheWarmupFailedException
	 */
	public function warmup(): void;

}
