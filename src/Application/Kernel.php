<?php

declare(strict_types=1);

namespace Tempest\Application;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use Tempest\AppConfig;
use Tempest\Container\Container;
use Tempest\Container\GenericContainer;
use Tempest\Database\PDOInitializer;
use Tempest\Http\RequestInitializer;
use Tempest\Http\RouteBindingInitializer;
use Tempest\Http\ServerInitializer;
use Throwable;

final readonly class Kernel
{
    public function __construct(
        public string $root,
        private AppConfig $appConfig,
        private array $bootstraps = [],
    ) {
    }

    public function init(): Container
    {
        $container = $this->createContainer();
        $container->config($this->appConfig);

        foreach ($this->bootstraps as $bootstrap) {
            $container->get($bootstrap)->boot();
        }

        $this->initDiscovery($container);

        return $container;
    }

    private function createContainer(): Container
    {
        $container = new GenericContainer();

        GenericContainer::setInstance($container);

        $container
            ->singleton(Kernel::class, fn () => $this)
            ->singleton(Container::class, fn () => $container)
            ->addInitializer(new ServerInitializer())
            ->addInitializer(new RequestInitializer())
            ->addInitializer(new RouteBindingInitializer())
            ->addInitializer(new PDOInitializer());

        /** @var class-string<\Tempest\Bootstraps\Bootstrap> $bootstrap */
        foreach ($this->bootstraps as $bootstrap) {
            $container->singleton($bootstrap, fn () => new $bootstrap($container));
        }

        return $container;
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
}
