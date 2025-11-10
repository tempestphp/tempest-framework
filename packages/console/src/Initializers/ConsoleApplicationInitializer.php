<?php

declare(strict_types=1);

namespace Tempest\Console\Initializers;

use Tempest\Console\ConsoleApplication;
use Tempest\Console\Input\ConsoleArgumentBag;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Core\Application;

final readonly class ConsoleApplicationInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): ConsoleApplication
    {
        $application = new ConsoleApplication(
            container: $container,
            argumentBag: $container->get(ConsoleArgumentBag::class),
        );

        $container->singleton(Application::class, fn () => $application);

        return $application;
    }
}
