<?php

declare(strict_types=1);

namespace Tempest\EventBus;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

#[Singleton]
final readonly class EventBusInitializer implements Initializer
{
    public function initialize(Container $container): EventBus
    {
        return new GenericEventBus(
            $container,
            $container->get(EventBusConfig::class),
        );
    }
}
