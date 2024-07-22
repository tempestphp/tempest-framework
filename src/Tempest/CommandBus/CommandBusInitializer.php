<?php

declare(strict_types=1);

namespace Tempest\CommandBus;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

#[Singleton]
final readonly class CommandBusInitializer implements Initializer
{
    public function initialize(Container $container): CommandBus
    {
        return $container->get(GenericCommandBus::class);
    }
}
