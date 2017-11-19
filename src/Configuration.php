<?php

declare(strict_types = 1);

namespace Oops\Morozko;


final class Configuration
{

	/**
	 * @var CacheWarmer[]
	 */
	private $cacheWarmers = [];


	public function addCacheWarmer(CacheWarmer $cacheWarmer)
	{
		$this->cacheWarmers[] = $cacheWarmer;
	}


	/**
	 * @return CacheWarmer[]
	 */
	public function getCacheWarmers(): array
	{
		return $this->cacheWarmers;
	}

}
