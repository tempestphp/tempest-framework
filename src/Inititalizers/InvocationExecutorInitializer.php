<?php

declare(strict_types=1);

namespace Tempest\Console\Inititalizers;

use Tempest\Application;
use Tempest\Console\ConsoleApplication;
use Tempest\Console\Scheduler\GenericInvocationExecutor;
use Tempest\Console\Scheduler\NullInvocationExecutor;
use Tempest\Console\Scheduler\ScheduledInvocationExecutor;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

#[Singleton]
final readonly class InvocationExecutorInitializer implements Initializer
{
    public function initialize(Container $container): ScheduledInvocationExecutor
    {
        $app = $container->get(Application::class);

        if (! $app instanceof ConsoleApplication) {
            $executor = new NullInvocationExecutor();
        } else {
            $executor = new GenericInvocationExecutor();
        }

        return $executor;
    }
}
