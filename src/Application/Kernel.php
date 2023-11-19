<?php

declare(strict_types=1);

namespace Tempest\Application;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use Tempest\Console\GenericConsoleFormatter;
use Tempest\Console\GenericConsoleOutput;
use Tempest\Container\GenericContainer;
use Tempest\Database\PDOInitializer;
use Tempest\Discovery\ConsoleCommandDiscoverer;
use Tempest\Discovery\ControllerDiscoverer;
use Tempest\Discovery\MigrationDiscoverer;
use Tempest\Http\GenericRouter;
use Tempest\Http\RequestInitializer;
use Tempest\Http\RouteBindingInitializer;
use Tempest\Http\ServerInitializer;
use Tempest\Interface\ConsoleFormatter;
use Tempest\Interface\ConsoleOutput;
use Tempest\Interface\Container;
use Tempest\Interface\Discoverer;
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

        $this->initDiscovery($rootDirectory, $rootNamespace, $container);

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
    }

    private function initDiscovery(
        string $rootDirectory,
        string $rootNamespace,
        Container $container,
    ): void {
        $directories = new RecursiveDirectoryIterator($rootDirectory);

        $files = new RecursiveIteratorIterator($directories);

        /** @var Discoverer[] $discoverers */
        $discoverers = [
            $container->get(ControllerDiscoverer::class),
            $container->get(MigrationDiscoverer::class),
            $container->get(ConsoleCommandDiscoverer::class),
        ];

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

            foreach ($discoverers as $discoverer) {
                $discoverer->discover($reflection);
            }
        }
    }
}
