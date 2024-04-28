<?php

declare(strict_types=1);

namespace Tempest\Console\Inititalizers;

use Tempest\Console\Scheduler\ScheduledCommandsResolver;
use Tempest\Console\Scheduler\SchedulerConfig;
use Tempest\Console\Scheduler\StatefulScheduledCommandsResolver;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

#[Singleton]
final readonly class ScheduledCommandsResolverInitializer implements Initializer
{
    public function initialize(Container $container): ScheduledCommandsResolver
    {
        return new StatefulScheduledCommandsResolver(
            $container->get(SchedulerConfig::class),
        );
    }
}
