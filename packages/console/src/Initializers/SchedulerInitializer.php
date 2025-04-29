<?php

declare(strict_types=1);

namespace Tempest\Console\Initializers;

use Tempest\Console\ConsoleApplication;
use Tempest\Console\Input\ConsoleArgumentBag;
use Tempest\Console\Scheduler;
use Tempest\Console\Scheduler\GenericScheduler;
use Tempest\Console\Scheduler\NullScheduler;
use Tempest\Console\Scheduler\SchedulerConfig;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Core\Application;
use Tempest\Core\ShellExecutor;

final readonly class SchedulerInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): Scheduler
    {
        $application = $container->get(Application::class);

        if (! ($application instanceof ConsoleApplication)) {
            return new NullScheduler();
        }

        return new GenericScheduler(
            $container->get(SchedulerConfig::class),
            $container->get(ConsoleArgumentBag::class),
            $container->get(ShellExecutor::class),
        );
    }
}
