<?php

declare(strict_types = 1);

namespace Oops\Morozko\Console;

use Oops\Morozko\Configuration;
use Oops\Morozko\CacheWarmupFailedException;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;


final class WarmupCommand extends Command
{

	private const OPTION_STRICT = 'strict';

	/**
	 * @var Configuration
	 */
	private $configuration;


	public function __construct(Configuration $configuration)
	{
		parent::__construct();
		$this->configuration = $configuration;
	}


	protected function configure()
	{
		$this->setName('oops:morozko:warmup')
			->setDescription('Warms up cache using a configured set of cache warmers.')
			->addOption(self::OPTION_STRICT, NULL, InputOption::VALUE_NONE, 'Returns a non-zero exit code if any of the cache warmers fails.');
	}


	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$strict = $input->getOption(self::OPTION_STRICT);
		$exitCode = 0;

		$logger = new ConsoleLogger($output);

		foreach ($this->configuration->getCacheWarmers() as $cacheWarmer) {
			if ($cacheWarmer instanceof LoggerAwareInterface) {
				$cacheWarmer->setLogger($logger);
			}

			try {
				$cacheWarmer->warmup();
				if ($output->isVerbose()) {
					$output->writeln(\sprintf(
						'<info>%s warmed up</info>',
						\get_class($cacheWarmer)
					));
				}

			} catch (CacheWarmupFailedException $e) {
				$output->writeln(\sprintf(
					'<error>%s failed: %s</error>',
					\get_class($cacheWarmer),
					$e->getMessage()
				));

				if ($strict) {
					$exitCode = 1;
				}
			}
		}

		return $exitCode;
	}

}
