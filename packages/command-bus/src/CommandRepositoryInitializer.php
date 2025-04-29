<?php

declare(strict_types=1);

namespace Tempest\CommandBus;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

final readonly class CommandRepositoryInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): CommandRepository
    {
        $commandRepositoryClass = $container->get(CommandBusConfig::class)->commandRepositoryClass;

        return $container->get($commandRepositoryClass);
    }
}
