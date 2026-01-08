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

Tempest will automatically discover all console commands from multiple sources:

1. **Core Tempest packages** — Built-in commands from Tempest itself
2. **Vendor packages** — Third-party packages that require `tempest/framework` or `tempest/core`
3. **App namespaces** — All namespaces configured as PSR-4 autoload paths in your `composer.json`

```json
{
	"autoload": {
		"psr-4": {
			"App\\": "app/"
		}
	}
}
```

In case you need more fine-grained control over which directories to discover, you may provide additional discovery locations to the `{php}ConsoleApplication::boot()` method:

```php
use Tempest\Console\ConsoleApplication;
use Tempest\Discovery\DiscoveryLocation;

ConsoleApplication::boot(
    discoveryLocations: [
        new DiscoveryLocation(
            namespace: 'MyApp\\',
            path: __DIR__ . '/src',
        ),
    ],
)->run();
```

The `{php}boot()` method accepts the following parameters:

- `{php}$name` — The application name (default: `'Tempest'`)
- `{php}$root` — The root directory (default: current working directory)
- `{php}$discoveryLocations` — Additional discovery locations to append to the auto-discovered ones
