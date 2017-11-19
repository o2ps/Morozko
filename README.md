# Oops/Morozko

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

Oops/Morozko provides a Symfony/Console command, thus, **you must also install and configure a Symfony/Console integration package** like [Kdyby/Console](https://github.com/Kdyby/Console) or [contributte/console](https://github.com/contributte/console).


## Usage

Register the extension in your config file. Don't forget to register a Symfony/Console integration as well:

```yaml
extensions:
    morozko: Oops\Morozko\DI\MorozkoExtension
    console: Kdyby\Console\DI\ConsoleExtension
```

Then you can directly run the `oops:morozko:warmup` command, or add it to your deploy process.


### Default cache warmers

By default, the `oops:morozko:warmup` command executes one default cache warmer: the `NetteConfiguratorCacheWarmer`, which compiles the DI container. To be independent on the server configuration, it does so by running a PHP built-in web server and dispatching an HTTP request to it.

The web server uses the `%wwwDir%` parameter as a document root. **Be aware that in CLI the `%wwwDir%` parameter might not actually point to the document root.** For example, if you run console commands via `bin/console.php`, it will likely point to the `bin` directory.
If that is your case, you need to set the correct parameter's value in your `bootstrap.php`:

```php
<?php
$configurator = new Nette\Configurator();
$configurator->addParameters([
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
