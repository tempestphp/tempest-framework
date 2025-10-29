---
title: Standalone components
---

## Overview

Many Tempest components can be installed as standalone packages in existing or new projects: `tempest/console`, `tempest/http`, `tempest/event-bus`, `tempest/debug`, `tempest/command-bus`, etc.

Note that Tempest is in its early stages—some components still depend on `tempest/core`, while they ideally should not. This may change in the future.

## `tempest/console`

```
composer require tempest/console
```

`tempest/console` ships with a built-in binary:

```console
./vendor/bin/tempest

<h1>Tempest</h1>

<comment>…</comment>
```

Or you can manually boot the console application like so:

```php
<?php

use \Tempest\Console\ConsoleApplication;

require_once __DIR__ . '/vendor/autoload.php';

ConsoleApplication::boot()->run();
```

## `tempest/http`

`tempest/http` contains all code to run a web application: router and view renderer, controllers, HTTP exception handling, view components, etc.

```
composer require tempest/http
```

Note that `tempest/console` is shipped with `tempest/http` as well so that you can manage discovery cache, static pages, debug routes, use the local dev server, etc.

You can install the necessary files with the built-in tempest console:

```console
./vendor/bin/tempest install framework
```

Or you can manually create an `index.php` file in your project's public folder:

```php
<?php
use \Tempest\Router\HttpApplication;

require_once __DIR__ . '/vendor/autoload.php';

HttpApplication::boot(
    root: __DIR__ . '/../',
)->run();
```

Note that the `root` path passed in `HttpApplication::boot` should point to your project's root folder.

## `tempest/container`

`tempest/container` is Tempest's standalone container implementation. Note that this package doesn't provide discovery, so initializers will need to be added manually.

```
composer require tempest/container
```

```php
$container = new Tempest\Container\GenericContainer();

$container->addInitializer(FooInitializer::class);

$foo = $container->get(Foo::class);
```

## `tempest/debug`

`tempest/debug` provides the `lw`, `ld` and `ll` functions. This package is truly standalone, but when installed in a Tempest project, it will also automatically write to configured log files.

```
composer require tempest/debug
```

```php
ld($variable);
```

## `tempest/view`

Tempest View can be used as a standalone package:

```
composer require tempest/view
```

```php
$container = Tempest::boot(__DIR__);

$view = view(__DIR__ . '/src/b.view.php');

echo $container->get(ViewRenderer::class)->render($view);
```

There are a couple of notes to make when running Tempest View as a standalone component:

- Any view files and components will be discovered and must be in a directory with a valid PSR-4 namespace. View files themselves don't need to have a namespace, though.
- View files are compiled and cached. You can manually enable or disable this cache by setting the `{env}{:hl-keyword:VIEW_CACHE:}` environment variable to `true` or `false`. By default, the view cache is disabled.
- Optionally, you can require `tempest/console`, which will provide you with the `vendor/bin/tempest view:clear` command to clear view caches. If you don't install `tempest/console`, you'll have to manually clear view caches on deployment by removing the `.tempest/cache/views` directory.

## `tempest/event-bus`

Tempest's event bus can be used as a standalone package, in order for event handlers to be discovered, you'll have to boot Tempest's kernel and resolve the event bus from the container:

```
composer require tempest/event-bus
```

```php
$container = Tempest::boot();

// You can manually resolve the event bus from the container
$eventBus = $container->get(\Tempest\EventBus\EventBus::class);
$eventBus->dispatch(new MyEvent());

// Or use the `event` function, which is shipped with the package
\Tempest\event(new MyEvent());
```

## `tempest/command-bus`

Tempest's command bus can be used as a standalone package, in order for command handlers to be discovered, you'll have to boot Tempest's kernel and resolve the command bus from the container:

```
composer require tempest/command-bus
```

```php
$container = Tempest::boot();

// You can manually resolve the command bus from the container
$commandBus = $container->get(\Tempest\CommandBus\CommandBus::class);
$commandBus->dispatch(new MyCommand());

// Or use the `command` function, which is shipped with the package
\Tempest\command(new \Brendt\MyEvent());
```

## `tempest/mapper`

`tempest/mapper` maps data between many types of sources, from arrays to objects, objects to JSON, …

```
composer require tempest/mapper
```

```php
Tempest::boot();

$foo = map(['name' => 'Hi'])->to(Foo::class);
```
