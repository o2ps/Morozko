<?php

declare(strict_types = 1);

namespace Oops\Morozko\CacheWarmers;

use Oops\Morozko\CacheWarmer;
use Oops\Morozko\CacheWarmupFailedException;
use PackageVersions\Versions;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;


final class NetteConfiguratorCacheWarmer implements CacheWarmer, LoggerAwareInterface
{

	use LoggerAwareTrait;


	/**
	 * @var string
	 */
	private $wwwDir;


	public function __construct(string $wwwDir)
	{
		$this->wwwDir = $wwwDir;
		$this->logger = new NullLogger();
	}


	public function warmup(): void
	{
		$port = $this->findAvailablePort();
		$webServer = $this->runWebServer($port);
		$this->dispatchRequest($port, $webServer);
	}


	private function findAvailablePort(): int
	{
		$this->logger->info('Finding available port.');

		for ($port = 8000; $port < 9000; $port++) {
			$this->logger->debug(\sprintf('Trying port %d.', $port));
			$socket = @\fsockopen('127.0.0.1', $port);
			if ($socket === FALSE) {
				$this->logger->debug(\sprintf('Port %d is available.', $port));
				return $port;
			}

			$this->logger->debug(\sprintf('Port %d is unavailable.', $port));
			\fclose($socket);
		}

		throw new CacheWarmupFailedException(
			'Cannot launch PHP built-in webserver: no available port in range 8000-8999'
		);
	}


	private function runWebServer(int $port): Process
	{
		$this->logger->info('Locating PHP executable.');
		$phpExecutable = (new PhpExecutableFinder())->find();
		if ($phpExecutable === FALSE) {
			throw new CacheWarmupFailedException(
				'Cannot launch PHP built-in webserver: cannot find PHP executable'
			);
		}

		$this->logger->debug(\sprintf('PHP executable found at %s.', $phpExecutable));

		try {
			$webServer = new Process(\sprintf(
				'%s -S 127.0.0.1:%d -t %s',
				$phpExecutable,
				$port,
				\escapeshellarg($this->wwwDir)
			));

			$this->logger->info(\sprintf('Starting PHP web-server process on port %d.', $port));
			$webServer->start();

			$start = \microtime(TRUE);
			$attempt = 1;
			$this->logger->debug(\sprintf('Waiting for PHP web-server to start listening on port %d.', $port));
			do {
				$timeTaken = \microtime(TRUE) - $start;
				if ($timeTaken > 5) {
					throw new CacheWarmupFailedException(
						\sprintf(
							'PHP web-server failed to start listening on port %d in %d attempts within 5 seconds, giving up.',
							$port,
							$attempt
						)
					);
				}

				\usleep(200 * 1000); // 200 ms
				\error_clear_last();
				$socket = @\fsockopen('127.0.0.1', $port);

				if ($socket === FALSE) {
					$error = \error_get_last();
					$this->logger->debug(\sprintf(
						'Cannot connect to 127.0.0.1:%d in attempt %d after %.3f seconds%s',
						$port,
						$attempt,
						$timeTaken,
						$error !== NULL ? \sprintf(': %s', \json_encode($error)) : '.'
					));
				}

				$attempt++;

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
		$this->logger->info(\sprintf('Dispatching an HTTP request to port %d.', $port));

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

		$httpCode = \curl_getinfo($curl, \CURLINFO_RESPONSE_CODE);
		$this->logger->debug(\sprintf(
			'HTTP request to the application returned status code %d',
			$httpCode
		));

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
