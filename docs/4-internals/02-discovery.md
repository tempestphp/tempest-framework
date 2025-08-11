---
title: Discovery
description: "Learn how Tempest automatically locates controller actions, event handlers, console commands, and other components of your application."
---

## Overview

Tempest introduces a unique approach to bootstrapping an application. Instead of requiring manual registration of project code and packages, Tempest automatically scans the codebase and detects the components that should be loaded. This process is called **discovery**.

Discovery is powered by composer metadata. Every package that depends on Tempest, along with your application's own code, are included in the discovery process. Tempest applies various rules to determine the purpose of different pieces of code. It can analyze file names, attributes, interfaces, return types, and more.

For instance, web routes are discovered based on route attributes:

```php app/HomeController.php
final readonly class HomeController
{
    #[Get(uri: '/home')]
    public function __invoke(): View
    {
        return view('home.view.php');
    }
}
```

Note that Tempest is able to cache discovery information to avoid any performance cost. Enabling this cache in production is highly recommended.

## Built-in discovery classes

Most of Tempest's features are built on top of discovery. The following describes which discovery class is associated to which feature.

- {`\Tempest\Core\DiscoveryDiscovery`} <br />
  Discovers other discovery classes. This class is run manually by the framework when booted.
- {`\Tempest\CommandBus\CommandBusDiscovery`} <br />
  Discovers methods with the `#[CommandHandler]` attribute and registers them into the command bus.
- {`\Tempest\Console\Discovery\ConsoleCommandDiscovery`} <br />
  Discovers methods with the `#[ConsoleCommand]` attribute and registers them as console commands.
- {`\Tempest\Console\Discovery\ScheduleDiscovery`} <br />
  Discovers methods with the `#[Schedule]` attribute and registers them as scheduled tasks.
- {`\Tempest\Container\InitializerDiscovery`} <br />
  Discovers classes that implement {b`\Tempest\Container\Initializer`} or {b`\Tempest\Container\DynamicInitializer`} and registers them in the container.
- {`\Tempest\Database\MigrationDiscovery`} <br />
  Discovers classes that implement {`\Tempest\Database\Migration`} and registers them in the migration manager.
- {`\Tempest\EventBusDiscovery\EventBusDiscovery`} <br />
  Discovers methods with the `#[EventHandler]` attribute and registers them in the event bus.
- {`\Tempest\Router\RouteDiscovery`} <br />
  Discovers route attributes on methods and registers them as controller actions in the router.
- {`\Tempest\Mapper\MapperDiscovery`} <br />
  Discovers classes that implement {`\Tempest\Mapper\Mapper`}, which are registered in `\Tempest\Mapper\ObjectFactory`
- {`\Tempest\View\ViewComponentDiscovery`} <br />
  Discovers classes that implement {`\Tempest\View\ViewComponent`}, as well as view files that contain `{html}<x-component>` or named `x-*.view.php`
- {`\Tempest\Vite\ViteDiscovery`} <br />
  Discovers `*.entrypoint.{ts,js,css}` files and register them as entrypoints.

## Implementing your own discovery

### Discovering code in classes

Tempest will discover classes that implement {`\Tempest\Discovery\Discovery`}. You may create one, and implement the `discover()` and `apply` methods.

The `discover()` method accepts a {b`Tempest\Core\DiscoveryLocation`} and a {b`Tempest\Reflection\ClassReflector`} parameter. You may use the latter to loop through a class' attributes, methods, parameters or anything else.

If you find what you are interested in, you may register it using `$this->discoveryItems->add()`. As an example, the following is a simplified version of the event bus discovery:

```php EventBusDiscovery.php
use Tempest\Discovery\Discovery;
use Tempest\Discovery\IsDiscovery;

final readonly class EventBusDiscovery implements Discovery
{
    // This provides the default implementation for `Discovery`'s internals
    use IsDiscovery;

    public function __construct(
        // Discovery classes are autowired,
        // so you can inject all dependencies you need
        private EventBusConfig $eventBusConfig,
    ) {
    }

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        foreach ($class->getPublicMethods() as $method) {
            $eventHandler = $method->getAttribute(EventHandler::class);

            // Extra checks to determine whether
            // we can actually use the current method as an event handler

            // …

            // Finally, we add all discovery-related data into `$this->discoveryItems`:
            $this->discoveryItems->add($location, [$eventName, $eventHandler, $method]);
        }
    }

    // Next, the `apply` method is called whenever discovery is ready to be
    // applied into the framework. In this case, we want to loop over all
    // registered discovery items, and add them to the event bus config.
    public function apply(): void
    {
        foreach ($this->discoveryItems as [$eventName, $eventHandler, $method]) {
            $this->eventBusConfig->addClassMethodHandler(
                event: $eventName,
                handler: $eventHandler,
                reflectionMethod: $method,
            );
        }
    }
}
```

### Discovering files

In some situations, you may want to not just discover classes, but also files. For instance, view files, front-end entrypoints or SQL migrations are not PHP classes, but still need to be discovered.

In this case, you may implement the additional {`\Tempest\Discovery\DiscoversPath`} interface. It will allow a discovery class to discover all paths that aren't classes as well. As an example, below is a simplified version of the Vite discovery:

```php
use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoversPath;
use Tempest\Discovery\IsDiscovery;

final class ViteDiscovery implements Discovery, DiscoversPath
{
    use IsDiscovery;

    public function __construct(
        private readonly ViteConfig $viteConfig,
    ) {}

    // We are not discovering any class, so we return immediately.
    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        return;
    }

    // This method is called for every file in registered discovery locations.
    // We can use the `$path` to determine whether we are interested in it.
    public function discoverPath(DiscoveryLocation $location, string $path): void
    {
        // We are insterested in `.ts`, `.css` and `.js` files only.
        if (! ends_with($path, ['.ts', '.css', '.js'])) {
            return;
        }

        // These files need to be specifically marked as `.entrypoint`.
        if (! str($path)->beforeLast('.')->endsWith('.entrypoint')) {
            return;
        }

        $this->discoveryItems->add($location, [$path]);
    }

    // When discovery is cached, `discover` and `discoverPath` are not called.
    // Instead, `discoveryItems` is already fed with serialized data, which
    // we can use. In this case, we add the paths to the Vite config.
    public function apply(): void
    {
        foreach ($this->discoveryItems as [$path]) {
            $this->viteConfig->addEntrypoint($path);
        }
    }
}
```

## Discovery in production

While discovery is a really powerful feature, it also comes with some performance considerations. In production environments, you need to make sure that the discovery workflow is cached. This is done by using the `DISCOVERY_CACHE` environment variable:

```env .env
{:hl-property:DISCOVERY_CACHE:}={:hl-keyword:true:}
```

The most important step is to generate that cache. This is done by running the `discovery:generate`, which should be part of your deployment pipeline. Make sure to run it before any other Tempest command.

```console
./tempest discovery:generate
 ℹ  Clearing existing discovery cache…
 ✓  Discovery cached has been cleared
 ℹ  Generating new discovery cache… (cache strategy used: all)
 ✓  Cached 1119 items
```

## Discovery for local development

By default, the discovery cache is disabled in a development environment. Depending on your local setup, it is likely that you will not run into noticeable slowdowns. However, for larger projects, you might benefit from enabling a partial discovery cache:

```env .env
{:hl-property:DISCOVERY_CACHE:}={:hl-keyword:partial:}
```

This caching strategy will only cache discovery for vendor files. For this reason, it is recommended to run `discovery:generate` after every composer update:

```json
{
	"scripts": {
		"post-package-update": [
			"php tempest discovery:generate"
		]
	}
}
```

:::info
Note that, if you've created your project using {`tempest/app`}, you'll have the `post-package-update` script already included. You may read the [internal documentation about discovery](../3-internals/02-discovery) to learn more.
:::

## Excluding files and classes from discovery

If needed, you can always exclude discovered files and classes by providing a discovery config file:

```php app/discovery.config.php
use Tempest\Core\DiscoveryConfig;

return new DiscoveryConfig()
    ->skipClasses(GlobalHiddenDiscovery::class)
    ->skipPaths(__DIR__ . '/../../Fixtures/GlobalHiddenPathDiscovery.php');
```
