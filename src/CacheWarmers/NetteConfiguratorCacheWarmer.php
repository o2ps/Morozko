<?php

declare(strict_types = 1);

namespace Oops\Morozko\CacheWarmers;

use Oops\Morozko\CacheWarmer;
use Oops\Morozko\CacheWarmupFailedException;
use PackageVersions\Versions;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;


final class NetteConfiguratorCacheWarmer implements CacheWarmer
{

	/**
	 * @var string
	 */
	private $wwwDir;


	public function __construct(string $wwwDir)
	{
		$this->wwwDir = $wwwDir;
	}


	public function warmup(): void
	{
		$port = $this->findAvailablePort();
		$webServer = $this->runWebServer($port);
		$this->dispatchRequest($port, $webServer);
	}


	private function findAvailablePort(): int
	{
		for ($port = 8000; $port < 9000; $port++) {
			$socket = @\fsockopen('127.0.0.1', $port);
			if ($socket === FALSE) {
				return $port;
			}

			\fclose($socket);
		}

		throw new CacheWarmupFailedException(
			'Cannot launch PHP built-in webserver: no available port in range 8000-8999'
		);
	}


	private function runWebServer(int $port): Process
	{
		$phpExecutable = (new PhpExecutableFinder())->find();
		if ($phpExecutable === FALSE) {
			throw new CacheWarmupFailedException(
				'Cannot launch PHP built-in webserver: cannot find PHP executable'
			);
		}

		try {
			$webServer = new Process(\sprintf(
				'%s -S 127.0.0.1:%d -t %s',
				$phpExecutable,
				$port,
				\escapeshellarg($this->wwwDir)
			));

			$webServer->start();

			// wait for the server to start listening
			do {
				\usleep(200 * 1000); // 200 ms
				$socket = @\fsockopen('127.0.0.1', $port);
			} while ($socket === FALSE);
			\fclose($socket);

			return $webServer;

		} catch (RuntimeException $e) {
			throw new CacheWarmupFailedException(
				\sprintf(
					'Cannot launch PHP built-in webserver: %s',
					$e->getMessage()
				),
				0,
				$e
			);
		}
	}


	private function dispatchRequest(int $port, Process $webServer): void
	{
		$curl = \curl_init(\sprintf('http://127.0.0.1:%d', $port));
		\curl_setopt($curl, \CURLOPT_FAILONERROR, TRUE);
		\curl_setopt($curl, \CURLOPT_RETURNTRANSFER, TRUE);
		\curl_setopt($curl, \CURLOPT_TIMEOUT, 60);
		\curl_setopt($curl, \CURLOPT_HTTPHEADER, ['X-Forwarded-For: 255.255.255.255']); // bypass debug mode detection
		\curl_setopt($curl, \CURLOPT_USERAGENT, \sprintf(
			'PHP %s/Morozko %s',
			\PHP_VERSION,
			Versions::getVersion('oops/morozko')
		));

		\curl_exec($curl);

		try {
			if (\curl_errno($curl) !== 0) {
				throw new CacheWarmupFailedException(\sprintf(
					'HTTP request to the application failed: %s',
					\curl_error($curl)
				));
			}

		} finally {
			\curl_close($curl);
			$webServer->stop();
		}
	}

}
