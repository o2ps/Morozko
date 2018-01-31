<?php

declare(strict_types = 1);

namespace OopsTests\Morozko;

use Oops\Morozko\CacheWarmer;
use Oops\Morozko\CacheWarmupFailedException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;


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


final class LoggingCacheWarmer implements CacheWarmer, LoggerAwareInterface {
	private $logger;

	public function warmup(): void
	{
	}

	public function setLogger(LoggerInterface $logger): void
	{
		$this->logger = $logger;
	}

	public function getLogger(): LoggerInterface
	{
		return $this->logger;
	}
}
