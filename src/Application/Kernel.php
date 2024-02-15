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
use Tempest\Interface\Router;
use function Tempest\path;
use Throwable;

final readonly class Kernel
{
    public function __construct(
        private AppConfig $appConfig,
    ) {
    }

    public function getDiscoveries(): array
    {
        return [
            RouteDiscovery::class,
            MigrationDiscovery::class,
            ConsoleCommandDiscovery::class,
            CommandBusDiscovery::class,
        ];
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
        $folders = [
            glob(__DIR__ . '/../Config/**.php'),
            glob(path($this->appConfig->appPath, 'Config/**.php')),
        ];

        foreach ($folders as $configFiles) {
            foreach ($configFiles as $configFile) {
                $configFile = require $configFile;

                $container->config($configFile);
            }
        }

        $container->config($this->appConfig);
    }

    private function initDiscovery(Container $container): void
    {
        $scanDirectories = [
            [$this->appConfig->appPath, $this->appConfig->appNamespace],
            [__DIR__ . '/../', '\\Tempest\\'],
        ];

        $discoveries = $this->getDiscoveries();

        foreach ($discoveries as $discoveryClass) {
            /** @var \Tempest\Interface\Discovery $discovery */
            $discovery = $container->get($discoveryClass);

            if ($this->appConfig->discoveryCache && $discovery->hasCache()) {
                $discovery->restoreCache($container);

                continue;
            }

            foreach ($scanDirectories as [$directory, $namespace]) {
                $directories = new RecursiveDirectoryIterator($directory);
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
                        [$directory, '/', '.php', '\\\\'],
                        [$namespace, '\\', '', '\\'],
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
