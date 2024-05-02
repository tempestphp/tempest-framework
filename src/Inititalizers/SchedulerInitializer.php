<?php

declare(strict_types=1);

namespace Tempest\Console\Inititalizers;

use Tempest\Application;
use Tempest\Console\ConsoleApplication;
use Tempest\Console\Scheduler\GenericScheduler;
use Tempest\Console\Scheduler\NullScheduler;
use Tempest\Console\Scheduler\ScheduledInvocationExecutor;
use Tempest\Console\Scheduler\Scheduler;
use Tempest\Console\Scheduler\SchedulerConfig;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

#[Singleton]
final readonly class SchedulerInitializer implements Initializer
{
    public function initialize(Container $container): Scheduler
    {
        $app = $container->get(Application::class);

        if (! $app instanceof ConsoleApplication) {
            $consoleInput = new NullScheduler();
        } else {
            $consoleInput = new GenericScheduler(
                $container->get(SchedulerConfig::class),
                $container->get(ScheduledInvocationExecutor::class),
            );
        }

        return $consoleInput;
    }
}
