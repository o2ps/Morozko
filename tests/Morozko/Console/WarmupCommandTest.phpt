<?php

declare(strict_types = 1);

namespace OopsTests\Morozko\Console;

use Oops\Morozko\Configuration;
use Oops\Morozko\Console\WarmupCommand;
use OopsTests\Morozko\FailingCacheWarmer;
use OopsTests\Morozko\LoggingCacheWarmer;
use OopsTests\Morozko\SuccessfulCacheWarmer;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Tester\Assert;
use Tester\TestCase;


require_once __DIR__ . '/../../bootstrap.php';


/**
 * @testCase
 */
final class WarmupCommandTest extends TestCase
{

	public function testBasic(): void
	{
		$collection = new Configuration();
		$collection->addCacheWarmer(new SuccessfulCacheWarmer());

		$input = new ArrayInput(['oops:morozko:warmup']);
		$output = new BufferedOutput(OutputInterface::VERBOSITY_NORMAL, FALSE);

		$exitCode = $this->runCommand($collection, $input, $output);
		Assert::same(0, $exitCode);
		Assert::same('', $output->fetch());
	}


	public function testVerbose(): void
	{
		$collection = new Configuration();
		$collection->addCacheWarmer(new SuccessfulCacheWarmer());

		$input = new ArrayInput(['oops:morozko:warmup']);
		$output = new BufferedOutput(OutputInterface::VERBOSITY_VERBOSE, FALSE);

		$exitCode = $this->runCommand($collection, $input, $output);
		Assert::same(0, $exitCode);
		Assert::same(SuccessfulCacheWarmer::class . " warmed up\n", $output->fetch());
	}


	public function testFailed(): void
	{
		$collection = new Configuration();
		$collection->addCacheWarmer(new FailingCacheWarmer());

		$input = new ArrayInput(['oops:morozko:warmup']);
		$output = new BufferedOutput(OutputInterface::VERBOSITY_NORMAL, FALSE);

		$exitCode = $this->runCommand($collection, $input, $output);
		Assert::same(0, $exitCode);
		Assert::same(FailingCacheWarmer::class . " failed: Cache warmup failed!\n", $output->fetch());
	}


	public function testStrict(): void
	{
		$collection = new Configuration();
		$collection->addCacheWarmer(new FailingCacheWarmer());

		$input = new ArrayInput(['oops:morozko:warmup', '--strict' => TRUE]);
		$output = new BufferedOutput(OutputInterface::VERBOSITY_NORMAL, FALSE);

		$exitCode = $this->runCommand($collection, $input, $output);
		Assert::same(1, $exitCode);
		Assert::same(FailingCacheWarmer::class . " failed: Cache warmup failed!\n", $output->fetch());
	}


	public function testLogger(): void
	{
		$collection = new Configuration();
		$cacheWarmer = new LoggingCacheWarmer();
		$collection->addCacheWarmer($cacheWarmer);

		$input = new ArrayInput(['oops:morozko:warmup']);
		$output = new BufferedOutput(OutputInterface::VERBOSITY_NORMAL, FALSE);

		$exitCode = $this->runCommand($collection, $input, $output);
		Assert::same(0, $exitCode);
		Assert::same('', $output->fetch());

		Assert::type(ConsoleLogger::class, $cacheWarmer->getLogger());
	}


	private function runCommand(
		Configuration $collection,
		InputInterface $input,
		OutputInterface $output
	): int
	{
		$command = new WarmupCommand($collection);
		$application = new Application();
		$application->setAutoExit(FALSE);
		$application->add($command);

		return $application->run($input, $output);
	}

}


(new WarmupCommandTest())->run();
