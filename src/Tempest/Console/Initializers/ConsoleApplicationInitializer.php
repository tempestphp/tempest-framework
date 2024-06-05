<?php

declare(strict_types=1);

namespace Tempest\Console\Initializers;

use Tempest\Application\Application;
use Tempest\Console\ConsoleApplication;
use Tempest\Console\Input\ConsoleArgumentBag;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

#[Singleton]
final readonly class ConsoleApplicationInitializer implements Initializer
{
    public function initialize(Container $container): ConsoleApplication
    {
        $argumentBag = new ConsoleArgumentBag($_SERVER['argv']);

        $container->singleton(ConsoleArgumentBag::class, fn () => $argumentBag);

        $application = new ConsoleApplication(
            container: $container,
            argumentBag: $argumentBag,
        );

        $container->singleton(Application::class, fn () => $application);

        return $application;
    }
}
