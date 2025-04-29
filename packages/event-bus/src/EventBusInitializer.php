<?php

declare(strict_types=1);

namespace Tempest\EventBus;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

final readonly class EventBusInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): EventBus
    {
        return new GenericEventBus(
            $container,
            $container->get(EventBusConfig::class),
        );
    }
}
