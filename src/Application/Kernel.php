<?php

declare(strict_types=1);

namespace Tempest\Application;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use Tempest\AppConfig;
use Tempest\Application\Exceptions\KernelException;
use Tempest\Commands\CommandBus;
use Tempest\Commands\GenericCommandBus;
use Tempest\Container\Container;
use Tempest\Container\GenericContainer;
use Tempest\Database\PDOInitializer;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Http\RequestInitializer;
use Tempest\Http\RouteBindingInitializer;
use Tempest\Http\ServerInitializer;
use function Tempest\path;
use Throwable;

final readonly class Kernel
{
    public function __construct(
        private string $root,
        private AppConfig $appConfig,
    ) {
    }

    public function init(): Container
    {
        $container = $this->initContainer();

        $this->initDiscoveryLocations();

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
            ->singleton(CommandBus::class, fn () => $container->get(GenericCommandBus::class))
            ->addInitializer(new ServerInitializer())
            ->addInitializer(new RequestInitializer())
            ->addInitializer(new RouteBindingInitializer())
            ->addInitializer(new PDOInitializer());

        return $container;
    }

    private function initDiscoveryLocations(): void
    {
        $this->discoverTempestNamespaces();
        $this->discoverInstalledPackageLocations();
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
        reset($this->appConfig->discoveryClasses);

        while ($discoveryClass = current($this->appConfig->discoveryClasses)) {
            /** @var \Tempest\Discovery\Discovery $discovery */
            $discovery = $container->get($discoveryClass);

            if ($this->appConfig->discoveryCache && $discovery->hasCache()) {
                $discovery->restoreCache($container);
                next($this->appConfig->discoveryClasses);

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

            next($this->appConfig->discoveryClasses);

            $discovery->storeCache();
        }
    }

    private function discoverInstalledPackageLocations(): void
    {
        $composerPath = path($this->root, 'vendor/composer');
        $installedPath = path($composerPath, 'installed.json');

        $installedJson = $this->loadJsonFile($installedPath);
        $packages = $installedJson['packages'] ?? [];
        foreach ($packages as $package) {
            $packagePath = path($composerPath, $package['install-path']);

            $requiresTempest = isset($package['require']['tempest/framework']);
            $hasPsr4Namespaces = isset($package['autoload']['psr-4']);
            if ($requiresTempest && $hasPsr4Namespaces) {
                foreach ($package['autoload']['psr-4'] as $namespace => $namespacePath) {
                    $namespacePath = path($packagePath, $namespacePath);
                    $this->addDiscoveryLocation($namespace, $namespacePath);
                }
            }
        }
    }

    private function discoverTempestNamespaces(): void
    {
        $composer = $this->loadJsonFile(path($this->root, 'composer.json'));

        $namespaceMap = $composer['autoload']['psr-4'] ?? [];
        foreach ($namespaceMap as $namespace => $path) {
            $path = path($this->root, $path);
            $this->addDiscoveryLocation($namespace, $path);
        }
    }

    private function addDiscoveryLocation(string $namespace, string $path): void
    {
        $this->appConfig->discoveryLocations[] = new DiscoveryLocation(
            namespace: $namespace,
            path     : $path,
        );
    }

    private function loadJsonFile(string $path): array
    {
        if (! is_file($path)) {
            $relativePath = str_replace($this->root, './', $path);

            throw new KernelException(sprintf('Could not locate %s, try running "composer install"', $relativePath));
        }

        return json_decode(file_get_contents($path), true);
    }
}
