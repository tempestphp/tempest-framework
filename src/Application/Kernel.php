<?php

declare(strict_types=1);

namespace Tempest\Application;

use Tempest\AppConfig;
use Tempest\Bootstraps\ConfigBootstrap;
use Tempest\Bootstraps\DiscoveryBootstrap;
use Tempest\Bootstraps\DiscoveryLocationBootstrap;
use Tempest\Container\Container;
use Tempest\Container\ContainerConfig;
use Tempest\Container\GenericContainer;
use Tempest\Database\PDOInitializer;
use Tempest\Http\RequestInitializer;
use Tempest\Http\RouteBindingInitializer;

final readonly class Kernel
{
    public function __construct(
        public string $root,
        private AppConfig $appConfig,
    ) {
    }

    public function init(): Container
    {
        $container = $this->createContainer();

        $bootstraps = [
            DiscoveryLocationBootstrap::class,
            ConfigBootstrap::class,
            DiscoveryBootstrap::class,
        ];

        foreach ($bootstraps as $bootstrap) {
            $container->get(
                $bootstrap,
                kernel: $this,
                appConfig: $this->appConfig,
            )->boot();
        }

        return $container;
    }

    private function createContainer(): Container
    {
        $container = new GenericContainer();

        GenericContainer::setInstance($container);

        $container
            ->config($this->appConfig)
            ->config(new ContainerConfig())
            ->singleton(self::class, fn () => $this)
            ->singleton(Container::class, fn () => $container)
            ->addInitializer(RequestInitializer::class)
            ->addInitializer(RouteBindingInitializer::class)
            ->addInitializer(PDOInitializer::class);

        return $container;
    }
}
