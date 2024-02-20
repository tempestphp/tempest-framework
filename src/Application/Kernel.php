<?php

declare(strict_types=1);

namespace Tempest\Application;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use Tempest\AppConfig;
use Tempest\Commands\GenericCommandBus;
use Tempest\Console\GenericConsoleFormatter;
use Tempest\Console\GenericConsoleInput;
use Tempest\Console\GenericConsoleOutput;
use Tempest\Container\GenericContainer;
use Tempest\Database\PDOInitializer;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Http\GenericRouter;
use Tempest\Http\RequestInitializer;
use Tempest\Http\RouteBindingInitializer;
use Tempest\Http\ServerInitializer;
use Tempest\Interface\CommandBus;
use Tempest\Interface\ConsoleFormatter;
use Tempest\Interface\ConsoleInput;
use Tempest\Interface\ConsoleOutput;
use Tempest\Interface\Container;
use Tempest\Interface\Router;
use function Tempest\path;
use Throwable;

final readonly class Kernel
{
    public function __construct(
        private string $root,
        private AppConfig $appConfig,
    ) {
    }

    public function getDiscovery(): array
    {
        $discovery = [];

        foreach ($this->appConfig->discoveryLocations as $package) {
            // TODO: scan for discovery classes
            $discovery = [...$discovery, ...$package->getDiscovery()];
        }

        return $discovery;
    }

    public function init(): Container
    {
        $container = $this->initContainer();

        $this->initPackages();

        $this->initConfig($container);

        $this->initDiscovery($container);

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

    private function initPackages(): void
    {
        $namespaces = require path($this->root, '/vendor/composer/autoload_psr4.php');

        foreach ($namespaces as $namespace => $path) {
            if (in_array($namespace, ['App\\', 'Src\\', 'Tempest\\'])) {
                $this->appConfig->discoveryLocations[] = new DiscoveryLocation(
                    namespace: $namespace,
                    path: $path[0],
                );
                
                continue;
            }

            // TODO: support other packages
        }
    }

    private function initConfig(Container $container): void
    {
        // Register AppConfig
        $container->config($this->appConfig);

        // Scan for package config files
        foreach ($this->appConfig->discoveryLocations as $package) {
            $configFiles = glob(path($package->path, 'Config/**.php'));

            foreach ($configFiles as $configFile) {
                $configFile = require $configFile;

                $container->config($configFile);
            }
        }
    }

    private function initDiscovery(Container $container): void
    {
        $discoveries = $this->getDiscovery();

        foreach ($discoveries as $discoveryClass) {
            /** @var \Tempest\Interface\Discovery $discovery */
            $discovery = $container->get($discoveryClass);

            if ($this->appConfig->discoveryCache && $discovery->hasCache()) {
                $discovery->restoreCache($container);

                continue;
            }

            foreach ($this->appConfig->discoveryLocations as $discoveryLocation) {
                $directories = new RecursiveDirectoryIterator($discoveryLocation->path);
                $files = new RecursiveIteratorIterator($directories);

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
                        [$discoveryLocation->path, '/', '.php', '\\\\'],
                        [$discoveryLocation->namespace, '\\', '', '\\'],
                        $file->getPathname(),
                    );

                    try {
                        $reflection = new ReflectionClass($className);
                    } catch (Throwable) {
                        continue;
                    }

                    $discovery->discover($reflection);
                }
            }

            $discovery->storeCache();
        }
    }
}
