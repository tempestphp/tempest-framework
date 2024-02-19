<?php

declare(strict_types=1);

namespace Tempest\Events;

use Tempest\Interface\Container;
use Tempest\Interface\EventBus;
use Tempest\Interface\Initializer;

final readonly class EventBusInitializer implements Initializer
{
    public function initialize(string $className, Container $container): object
    {
        $eventBus = new GenericEventBus(
            $container,
            $container->get(EventBusConfig::class),
        );

        $container->singleton(EventBus::class, fn () => $eventBus);

        return $eventBus;
    }
}
