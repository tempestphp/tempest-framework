<?php

declare(strict_types=1);

namespace Tempest\Framework\Application;

use Tempest\Console\ConsoleApplication;
use Tempest\Console\Input\ConsoleArgumentBag;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

final readonly class ApplicationInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): Application
    {
        if (isset($_SERVER['argv'])) {
            return new ConsoleApplication(
                container: $container,
                argumentBag: new ConsoleArgumentBag(['argv']),
            );
        }

        return new HttpApplication(
            container: $container,
            appConfig: $container->get(AppConfig::class),
        );
    }
}
