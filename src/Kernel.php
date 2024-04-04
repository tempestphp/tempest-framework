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
        private CoreConfig $coreConfig,
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
                coreConfig: $this->coreConfig,
            )->boot();
        }

        return $container;
    }

    private function createContainer(): Container
    {
        $container = new GenericContainer();

        GenericContainer::setInstance($container);

        $container
            ->config($this->coreConfig)
            ->singleton(self::class, fn () => $this)
            ->singleton(Container::class, fn () => $container);

        return $container;
    }
}
