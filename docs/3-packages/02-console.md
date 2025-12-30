---
title: Console
description: "The console component can be used as a standalone package to build console applications."
---

## Installation and usage

Tempest's console component can be used standalone. You simply need to require the `tempest/console` package:

```sh
composer require tempest/console
```

Once installed, you may boot a console application as follows.

```php ./my-cli
{:hl-comment:#!/usr/bin/env php:}
<?php

use Tempest\Console\ConsoleApplication;

require_once __DIR__ . '/vendor/autoload.php';

ConsoleApplication::boot()->run();
```

## Registering commands

`tempest/console` relies on [discovery](../1-essentials/05-discovery.md) to find and register console commands. That means you don't have to register any commands manually, and any method within your codebase using the `{php}#[ConsoleCommand]` attribute will automatically be discovered by your console application.

You may read more about building commands in the [dedicated documentation](../1-essentials/04-console-commands.md).

## Configuring discovery

Tempest will discover all console commands within namespaces configured as valid PSR-4 namespaces, as well as all third-party packages that require Tempest.

```json
{
	"autoload": {
		"psr-4": {
			"App\\": "app/"
		}
	}
}
```

In case you need more fine-grained control over which directories to discover, you may provide a custom {`Tempest\Core\AppConfig`} instance to the `{php}ConsoleApplication::boot()` method:

```php
use Tempest\AppConfig;
use Tempest\Core\DiscoveryLocation;
use Tempest\Console\ConsoleApplication;

$appConfig = new AppConfig(
    discoveryLocations: [
        new DiscoveryLocation(
            namespace: 'App\\',
            path: __DIR__ . '/app/',
        ),
    ],
);

ConsoleApplication::boot(appConfig: $appConfig)->run();
```
