<?php

declare(strict_types=1);

namespace Tempest;

use Tempest\Bootstraps\ConfigBootstrap;
use Tempest\Bootstraps\DiscoveryBootstrap;
use Tempest\Bootstraps\DiscoveryLocationBootstrap;
use Tempest\Container\Container;
use Tempest\Container\GenericContainer;

final readonly class Kernel
{
    public function __construct(
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
            ->singleton(self::class, fn () => $this)
            ->singleton(Container::class, fn () => $container);

        return $container;
    }
}
