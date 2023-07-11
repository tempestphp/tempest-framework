<?php

namespace Tempest;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use Tempest\Container\GenericContainer;
use Tempest\Discovery\ControllerDiscoverer;
use Tempest\Interfaces\Container;
use Tempest\Interfaces\Discoverer;
use Tempest\Interfaces\Router;
use Tempest\Http\GenericRouter;
use Tempest\Http\RequestInitializer;
use Throwable;

final readonly class Kernel
{
    public function init(
        string $rootDirectory = '/app',
        string $rootNamespace = 'App\\',
    ): Container
    {
        $container = $this->initContainer();

        $this->initConfig($rootDirectory, $container);

        $this->initDiscovery($rootDirectory, $rootNamespace, $container);

        return $container;
    }

    private function initContainer(): Container
    {
        $container = new GenericContainer();

        $container
            ->singleton(Kernel::class, fn() => $this)
            ->singleton(Container::class, fn() => $container)
            ->singleton(Router::class, fn(Container $container) => $container->get(GenericRouter::class))
            ->addInitializer(new RequestInitializer());

        return $container;
    }

    private function initConfig(string $rootDirectory, Container $container): void
    {
        $configFiles = glob(path($rootDirectory, 'Config/**.php'));

        foreach ($configFiles as $configFile) {
            $configFile = require $configFile;

            $container->config($configFile);
        }
    }

    private function initDiscovery(
        string $rootDirectory,
        string $rootNamespace,
        Container $container,
    ): void
    {
        $directories = new RecursiveDirectoryIterator($rootDirectory);

        $files = new RecursiveIteratorIterator($directories);

        /** @var Discoverer[] $discoverers */
        $discoverers = [
            $container->get(ControllerDiscoverer::class),
        ];

        foreach ($files as $file) {
            $className = str_replace(
                [$rootDirectory, '/', '.php', '\\\\'],
                [$rootNamespace, '\\', '', '\\'],
                $file,
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