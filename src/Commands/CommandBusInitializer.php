<?php

declare(strict_types=1);

namespace Tempest\Commands;

use Tempest\Container\Container;
use Tempest\Container\Initializer;

final readonly class CommandBusInitializer implements Initializer
{
    public function initialize(Container $container): object
    {
        $commandBus = $container->get(GenericCommandBus::class);

        $container->singleton(CommandBus::class, fn () => $commandBus);

        return $commandBus;
    }
}
