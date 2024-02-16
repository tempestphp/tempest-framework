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
        private AppConfig $appConfig,
    ) {
    }

    public function getDiscovery(): array
    {
        $discovery = [];

        foreach ($this->appConfig->packages as $package) {
            $discovery = [...$discovery, ...$package->getDiscovery()];
        }

        return $discovery;
    }

    public function init(): Container
    {
        $container = $this->initContainer();

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

    private function initConfig(Container $container): void
    {
        // Register AppConfig
        $container->config($this->appConfig);

        // Scan for package config files
        foreach ($this->appConfig->packages as $package) {
            $configFiles = glob(path($package->getPath(), 'Config/**.php'));

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

            foreach ($this->appConfig->packages as $package) {
                $directories = new RecursiveDirectoryIterator($package->getPath());
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
                        [$package->getPath(), '/', '.php', '\\\\'],
                        [$package->getNamespace(), '\\', '', '\\'],
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
