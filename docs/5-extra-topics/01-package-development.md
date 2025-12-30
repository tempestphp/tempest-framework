---
title: Package development
description: "Tempest comes with a handful of tools to help third-party package developers."
---

## Overview

Creating a package for Tempest is as simple as adding `tempest/core` as a dependency. When this happens, [discovery](../1-essentials/05-discovery.md) will find the package thanks to composer metadata and register discoverable classes.

Unlike Symfony or Laravel, Tempest doesn't have a dedicated "service provider" concept. Instead, you're encouraged to rely on [discovery](../1-essentials/05-discovery.md) and [initializers](../1-essentials/05-container#dependency-initializers).

## Preventing discovery

You may create classes which would normally be discovered by Tempest. You may prevent this behavior by marking them with the {`Tempest\Discovery\SkipDiscovery`} attribute.

You may still use that class internally, or allow you package to publish it using an [installer](#installers).

```php
use Tempest\Discovery\SkipDiscovery;

#[SkipDiscovery]
final readonly class UserMigration implements Migration
{
    // …
}
```

## Installers

An installer is a command that publishes files to the user's project. For instance, this can be used to export migration files that shouldn't be discovered unless the user have published them.

You may create an installed by implementing the {`Tempest\Core\Installer`} interface. Usually, the {`Tempest\Core\PublishesFiles`} trait is used to help with this task. This trait provides a convenient way to publish files and adjust their imports automatically.

### Publishing files

The `publish()` method from the {b`Tempest\Core\PublishesFiles`} trait allows for copying a file to the user's project. It will automatically adjust the file's imports, so that they point to the correct namespace.

The user will have a chance to specify the destination of the file, and whether or not to overwrite it.

```php
use Tempest\Core\Installer;
use Tempest\Core\PublishesFiles;
use Tempest\Discovery\SkipDiscovery;
use Tempest\Generation\ClassManipulator;
use function Tempest\src_namespace;
use function Tempest\src_path;

final readonly class AuthInstaller implements Installer
{
    use PublishesFiles;

    public function getName(): string
    {
        return 'auth';
    }

    public function install(): void
    {
        $publishFiles = [
            __DIR__ . '/User.php' => src_path('User.php'),
            __DIR__ . '/UserMigration.php' => src_path('UserMigration.php'),
            __DIR__ . '/Permission.php' => src_path('Permission.php'),
            __DIR__ . '/PermissionMigration.php' => src_path('PermissionMigration.php'),
            __DIR__ . '/UserPermission.php' => src_path('UserPermission.php'),
            __DIR__ . '/UserPermissionMigration.php' => src_path('UserPermissionMigration.php'),
        ];

        foreach ($publishFiles as $source => $destination) {
            $this->publish(
                source: $source,
                destination: $destination,
            );
        }

        $this->publishImports();
    }
}
```

### Customizing the publishing process

You may provide a callback to the `publish()` method to customize the publishing process. This callback will be called after the file has been copied, but before the imports have been adjusted.

```php
public function install(): void
{
    // …

    $this->publish(
        source: $source,
        destination: $destination,
        callback: function (string $source, string $destination): void {
            // …
        },
    );

    $this->publishImports();
}
```

### Ensuring correct imports

When publishing files using the `publish()` method, namespaces are not updated automatically.

This needs to be done by calling the `publishImports()` method. This method will loop over all published files, and adjust any import that references published files.

## Provider classes

Unlike Symfony or Laravel, Tempest doesn't have a dedicated "service provider" concept. Instead, you're encouraged to rely on discovery and initializers. However, there might be situations where you need to set up things for your package.

In order to do that, you may register a listener for the `KernelEvent::BOOTED` event. This event is triggered when Tempest's kernel has booted, but before any application code is run. It's the perfect place to hook into Tempest's internals if you need to set up stuff specifically for your package.

```php
use Tempest\Core\KernelEvent;
use Tempest\EventBus\EventHandler;

final readonly class MyPackageProvider
{
    public function __construct(
        // You can inject any dependency you like
        private Container $container,
    ) {}

    #[EventHandler(KernelEvent::BOOTED)]
    public function initialize(): void
    {
        // Do whatever needs to be done
        $this->container->…
    }
}
```

## Testing helpers

Tempest provides a {`\Tempest\Framework\Testing\IntegrationTest`} class, which your PHPUnit tests may extend from. By doing so, your tests will automatically boot the framework, and have a range of helper methods available.

For more information regarding testing, you may read the [dedicated documentation](../1-essentials/07-testing.md).
