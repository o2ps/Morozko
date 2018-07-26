# Oops/Morozko

:warning: **THIS PACKAGE IS NO LONGER MAINTAINED.** You can use the AdvancedCache module from [contributte/console-extra](https://github.com/contributte/console-extra) instead.

[![Build Status](https://img.shields.io/travis/o2ps/Morozko.svg)](https://travis-ci.org/o2ps/Morozko)
[![Downloads this Month](https://img.shields.io/packagist/dm/oops/morozko.svg)](https://packagist.org/packages/oops/morozko)
[![Latest stable](https://img.shields.io/packagist/v/oops/morozko.svg)](https://packagist.org/packages/oops/morozko)

![Morozko, the impersonation of frost from a 1964 Soviet movie](morozko.jpg)

> Are you warm yet, my little application?

Morozko is a utility for cache warmup in a Nette Framework application.


## Installation and requirements

```bash
$ composer require oops/morozko
```

Oops/Morozko requires PHP >= 7.1.

Oops/Morozko provides a Symfony/Console command, thus, **you must also install and configure a Symfony/Console integration package** such as [Kdyby/Console](https://github.com/Kdyby/Console) or [contributte/console](https://github.com/contributte/console).


## Usage

Register the extension in your config file. Don't forget to register a Symfony/Console integration as well:

```yaml
extensions:
    morozko: Oops\Morozko\DI\MorozkoExtension
    console: Kdyby\Console\DI\ConsoleExtension
```

Then you can directly run the `oops:morozko:warmup` command, or add it to your deploy process.


### Default cache warmers

By default, the `oops:morozko:warmup` command executes one default cache warmer: the `NetteConfiguratorCacheWarmer`, which compiles the DI container.

To be able to do so, it needs to know how your DI container is configured - you need to provide it with an implementation of `ConfiguratorFactoryInterface` whose `create()` method should return the same `Configurator` as in your application's `bootstrap.php`. Actually, it might be wise to use the implementation in `bootstrap.php` to prevent code duplication.

```php
<?php
namespace My\Application;

use Nette\Configurator;
use Oops\Morozko\CacheWarmers\NetteConfiguratorCacheWarmer\ConfiguratorFactoryInterface;

final class ConfiguratorFactory implements ConfiguratorFactoryInterface
{
    public function create(): Configurator
    {
        $configurator = new Configurator();
        // ... configure application ...
        return $configurator;
    }
}
```

```yaml
morozko:
    configuratorFactory: My\Application\ConfiguratorFactory
```


**Be aware that in CLI the `%wwwDir%` and `%appDir%` parameters might not actually point to the document root.** For example, if you run console commands via `bin/console.php`, it will likely point to the `bin` directory.
If that is your case, you need to set the parameters to the correct values in your configurator factory, otherwise the generated DI container won't match the one actually used in production:

```php
<?php
$configurator = new Nette\Configurator();
$configurator->addParameters([
    'appDir' => __DIR__,
    'wwwDir' => __DIR__ . '/../www',    
]);

// ...
```


### Additional cache warmers

There are currently these official additions to the Morozko's DI container cache warmer:

- [`oops/morozko-latte-bridge`](https://github.com/o2ps/MorozkoLatteBridge) pre-compiles all Latte templates found within a configured directory.
- [`oops/morozko-doctrine-bridge`](https://github.com/o2ps/MorozkoDoctrineBridge) warms up Doctrine metadata cache and generates entity proxies.


### Custom cache warmers

You can also create your own cache warmers. Simply create a class implementing the `Oops\Morozko\CacheWarmer` interface and register it as a service into the DI container.


### Third-party cache warmers

If you have a cache warmer that you'd like to share with others, feel free to add it to this section via a pull request.
