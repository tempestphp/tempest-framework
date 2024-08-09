<?php

declare(strict_types=1);

namespace Tempest\Console\Initializers;

use Tempest\Console\ConsoleApplication;
use Tempest\Console\Scheduler\GenericShellExecutor;
use Tempest\Console\Scheduler\NullShellExecutor;
use Tempest\Console\ShellExecutor;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Framework\Application\Application;

final readonly class InvocationExecutorInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): ShellExecutor
    {
        $app = $container->get(Application::class);

        if (! $app instanceof ConsoleApplication) {
            return new NullShellExecutor();
        }

        return new GenericShellExecutor();
    }
}
