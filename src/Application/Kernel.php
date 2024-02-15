<?php

declare(strict_types=1);

namespace Tempest\Application;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use Tempest\AppConfig;
use Tempest\Bus\GenericCommandBus;
use Tempest\Console\GenericConsoleFormatter;
use Tempest\Console\GenericConsoleInput;
use Tempest\Console\GenericConsoleOutput;
use Tempest\Container\GenericContainer;
use Tempest\Database\PDOInitializer;
use Tempest\Discovery\CommandBusDiscovery;
use Tempest\Discovery\ConsoleCommandDiscovery;
use Tempest\Discovery\MigrationDiscovery;
use Tempest\Discovery\RouteDiscovery;
use Tempest\Http\GenericRouter;
use Tempest\Http\RequestInitializer;
use Tempest\Http\RouteBindingInitializer;
use Tempest\Http\ServerInitializer;
use Tempest\Interface\CommandBus;
use Tempest\Interface\ConsoleFormatter;
use Tempest\Interface\ConsoleInput;
use Tempest\Interface\ConsoleOutput;
use Tempest\Interface\Container;
use Tempest\Interface\Discovery;
use Tempest\Interface\Router;

use function Tempest\path;

use Throwable;

final readonly class Kernel
{
    public function init(
        string $rootDirectory,
        string $rootNamespace,
    ): Container {
        $container = $this->initContainer();

        $this->initConfig($rootDirectory, $container);

        $this->initDiscovery(
            rootDirectory: $rootDirectory,
            rootNamespace: $rootNamespace,
            container: $container,
            useCache: false,
        );

        $this->initDiscovery(
            rootDirectory: __DIR__ . '/../',
            rootNamespace: '\\Tempest\\',
            container: $container,
            useCache: false,
        );

        return $container;
    }

    private function initContainer(): Container
    {
        $container = new GenericContainer();

        GenericContainer::setInstance($container);

        $container
            ->singleton(Kernel::class, fn () => $this)
            ->singleton(Container::class, fn () => $container)
            ->singleton(Router::class, fn (Container $container) => $container->get(GenericRouter::class))
            ->singleton(ConsoleFormatter::class, fn () => $container->get(GenericConsoleFormatter::class))
            ->singleton(ConsoleOutput::class, fn () => $container->get(GenericConsoleOutput::class))
            ->singleton(ConsoleInput::class, fn () => $container->get(GenericConsoleInput::class))
            ->singleton(CommandBus::class, fn () => $container->get(GenericCommandBus::class))
            ->addInitializer(new ServerInitializer())
            ->addInitializer(new RequestInitializer())
            ->addInitializer(new RouteBindingInitializer())
            ->addInitializer(new PDOInitializer());

        return $container;
    }

    private function initConfig(string $rootDirectory, Container $container): void
    {
        $folders = [
            glob(__DIR__ . '/../Config/**.php'),
            glob(path($rootDirectory, 'Config/**.php')),
        ];

        foreach ($folders as $configFiles) {
            foreach ($configFiles as $configFile) {
                $configFile = require $configFile;

                $container->config($configFile);
            }
        }

        // Automatically resolve AppConfig when not provided
        try {
            $container->get(AppConfig::class);
        } catch (Throwable) {
            $container->config(new AppConfig(
                rootPath: $rootDirectory,
            ));
        }
    }

    private function initDiscovery(
        string $rootDirectory,
        string $rootNamespace,
        Container $container,
        bool $useCache,
    ): void {
        $directories = new RecursiveDirectoryIterator($rootDirectory);

        $files = new RecursiveIteratorIterator($directories);

        /** @var Discovery[] $discoveries */
        $discoveries = [
            $container->get(RouteDiscovery::class),
            $container->get(MigrationDiscovery::class),
            $container->get(ConsoleCommandDiscovery::class),
            $container->get(CommandBusDiscovery::class),
        ];

        foreach ($discoveries as $discovery) {
            if ($useCache && $discovery->hasCache()) {
                $discovery->restoreCache($container);

                continue;
            }

            /** @var \SplFileInfo $file */
            foreach ($files as $file) {
                $fileName = $file->getFilename();

                if (
                    $fileName === ''
                    || $fileName === '.'
                    || $fileName === '..'
                    || ucfirst($fileName) !== $fileName
                ) {
                    continue;
                }

                $className = str_replace(
                    [$rootDirectory, '/', '.php', '\\\\'],
                    [$rootNamespace, '\\', '', '\\'],
                    $file->getPathname(),
                );

                try {
                    $reflection = new ReflectionClass($className);
                } catch (Throwable) {
                    continue;
                }

                $discovery->discover($reflection);
            }

            $discovery->storeCache();
        }
    }
}
