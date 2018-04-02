<?php

declare(strict_types = 1);

namespace Oops\Morozko\DI;


final class ConfigurationException extends \LogicException
{

	public static function missingConfiguratorFactory(string $extensionName): self
	{
		return new self(\sprintf(
			"Configuration option '%s.configuratorFactory' is missing.",
			$extensionName
		));
	}

}
