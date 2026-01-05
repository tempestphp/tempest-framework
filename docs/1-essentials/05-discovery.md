---
title: Discovery
description: "Tempest automatically locates controller actions, event handlers, console commands, and other components of your application, without needing any configuration from you."
---

## Overview

Tempest introduces a unique approach to bootstrapping applications. Instead of requiring manual registration of project code and packages, Tempest automatically scans the codebase and detects the components that should be loaded. This process is called **discovery**.

Discovery is powered by composer metadata. Every package that depends on Tempest, along with your application's own code, are included in the discovery process.

Tempest applies [various rules](#built-in-discovery-classes) to determine the purpose of different pieces of code—it can analyze file names, attributes, interfaces, return types, and more. For instance, web routes are discovered when methods are annotated with route attributes:

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

:::tip
Read the [getting started with discovery](/blog/discovery-explained) guide if you want to know more about the philosophy of discovery and how it works.
:::

## Discovery in production

Discovery comes with performance considerations. In production, it is always cached to avoid scanning files on every request.

To ensure that the discovery cache is up-to-date, add the `discovery:generate` command before any other Tempest command in your deployment pipeline.

```console ">_ ./tempest discovery:generate --no-interaction"
Clearing discovery cache <dim>.....................................</dim> <strong>2025-12-30 15:51:46</strong>
Clearing discovery cache <dim>.....................................</dim> <strong>DONE</strong>
Generating discovery cache using the `full` strategy <dim>.........</dim> <strong>2025-12-30 15:51:46</strong>
Generating discovery cache using the `full` strategy <dim>.........</dim> <strong>DONE</strong>
```

## Discovery for local development

During development, discovery is only enabled for application code. This implies that the cache should be regenerated whenever a package is installed or updated.

It is recommended to add the `discovery:generate` command to the `post-package-update` script in `composer.json`:

```json composer.json
{
	"scripts": {
		"post-package-update": [
			"@php tempest discovery:generate"
		]
	}
}
```

### Disabling discovery cache

In some situations, you may want to enable discovery even for vendor code. For instance, if you are working on a third-party package that is being developed alongside your application, you may want to have discovery enabled all the time.

To achieve this, set the `DISCOVERY_CACHE` environment variable to `false`:

```env .env
{:hl-property:DISCOVERY_CACHE:}={:hl-keyword:false:}
```

### Troubleshooting

The `discovery:clear` command clears the discovery cache, which will be rebuilt the next time the framework boots. `discovery:generate` can be used to manually regenerate the cache.

If the discovery cache gets corrupted and even `discovery:clear` is not enough, the `.tempest/cache/discovery` may be manually deleted from your project.

## Implementing your own discovery

While Tempest provides a variety of [built-in discovery classes](#built-in-discovery-classes), you may want to implement your own to extend the framework's capabilities in your application or in a package you are building.

### Discovering code in classes

Tempest discovers classes that implement {b`Tempest\Discovery\Discovery`}, which requires implementing the `discover()` and `apply()` methods. The {b`Tempest\Discovery\IsDiscovery`} trait provides the rest of the implementation.

The `discover()` method accepts a {b`Tempest\Core\DiscoveryLocation`} and a {b`Tempest\Reflection\ClassReflector`} parameter. The reflector can be used to loop through a class' attributes, methods, parameters or anything else. If the class matches your expectations, you may register it using `$this->discoveryItems->add()`.

As an example, the following is a simplified version of the event bus discovery:

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

It is possible to discover files instead of classes. For instance, view files, front-end entrypoints or SQL migrations are not PHP classes, but still need to be discovered.

In this case, you may implement the additional {b`\Tempest\Discovery\DiscoversPath`} interface. It requires a `discoverPath()` method that accepts a {b`Tempest\Core\DiscoveryLocation`} and a string path.

The example below shows a simplified version of the Vite entrypoint discovery:

```php ViteDiscovery.php
use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoversPath;
use Tempest\Discovery\IsDiscovery;
use Tempest\Support\Str;

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
        if (! Str\ends_with($path, ['.ts', '.css', '.js'])) {
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

## Excluding files and classes from discovery

Files and classes may be excluded from discovery by providing a {b`Tempest\Core\DiscoveryConfig`} [configuration](./06-configuration.md) file.

```php src/discovery.config.php
use Tempest\Core\DiscoveryConfig;

return new DiscoveryConfig()
    ->skipClasses(GlobalHiddenDiscovery::class)
    ->skipPaths(__DIR__ . '/../../Fixtures/GlobalHiddenPathDiscovery.php');
```

## Built-in discovery classes

Most of Tempest's features are built on top of discovery. The following is a non-exhaustive list that describes which discovery class is associated to which feature.

- {b`Tempest\Core\DiscoveryDiscovery`} discovers other discovery classes. This class is run manually by the framework when booted.
- {b`Tempest\CommandBus\CommandBusDiscovery`} discovers methods with the {b`#[Tempest\CommandBus\CommandHandler]`} attribute and registers them into the [command bus](../2-features/10-command-bus.md).
- {b`Tempest\Console\Discovery\ConsoleCommandDiscovery`} discovers methods with the {b`#[Tempest\Console\ConsoleCommand]`} attribute and registers them as [console commands](../1-essentials/04-console-commands.md).
- {b`Tempest\Console\Discovery\ScheduleDiscovery`} discovers methods with the {b`#[Tempest\Console\Schedule]`} attribute and registers them as [scheduled tasks](../2-features/11-scheduling.md).
- {b`Tempest\Container\InitializerDiscovery`} discovers classes that implement {b`\Tempest\Container\Initializer`} or {b`\Tempest\Container\DynamicInitializer`} and registers them as [dependency initializers](./05-container.md#dependency-initializers).
- {b`Tempest\Database\MigrationDiscovery`} discovers classes that implement {b`Tempest\Database\MigratesUp`} or {b`Tempest\Database\MigratesDown`} and registers them as [migrations](./03-database.md#migrations).
- {b`Tempest\EventBusDiscovery\EventBusDiscovery`} discovers methods with the {b`#[Tempest\EventBus\EventHandler]`} attribute and registers them in the [event bus](../2-features/08-events.md).
- {b`Tempest\Router\RouteDiscovery`} discovers route attributes on methods and registers them as [controller actions](./01-routing.md) in the router.
- {b`Tempest\Mapper\MapperDiscovery`} discovers classes that implement {b`Tempest\Mapper\Mapper`} and registers them for [mapping](../2-features/01-mapper.md#mapper-discovery).
- {b`Tempest\Mapper\CasterDiscovery`} discovers classes that implement {b`Tempest\Mapper\DynamicCaster`} and registers them as [casters](../2-features/01-mapper.md#casters-and-serializers).
- {b`Tempest\Mapper\SerializerDiscovery`} discovers classes that implement {b`Tempest\Mapper\DynamicSerializer`} and registers them as [serializers](../2-features/01-mapper.md#casters-and-serializers).
- {b`Tempest\View\ViewComponentDiscovery`} discovers `x-*.view.php` files and registers them as [view components](../1-essentials/02-views.md#view-components).
- {b`Tempest\Vite\ViteDiscovery`} discovers `*.entrypoint.{ts,js,css}` files and register them as [entrypoints](../2-features/02-asset-bundling.md#entrypoints).
- {b`Tempest\Auth\AccessControl\PolicyDiscovery`} discovers methods annotated with the {b`#[Tempest\Auth\AccessControl\Policy]`} attribute and registers them as [access control policies](../2-features/04-authentication.md#access-control).
- {b`Tempest\Core\InsightsProviderDiscovery`} discovers classes that implement {b`Tempest\Core\InsightsProvider`} and registers them as insights providers, which power the `tempest about` command.
