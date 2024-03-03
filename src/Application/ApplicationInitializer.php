<?php

declare(strict_types=1);

namespace Tempest\Application;

use Tempest\AppConfig;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

#[Singleton]
final readonly class ApplicationInitializer implements Initializer
{
    public function initialize(Container $container): Application
    {
        if (isset($_SERVER['argv'])) {
            $application = new ConsoleApplication(
                $_SERVER['argv'],
                $container,
                $container->get(AppConfig::class),
            );
        } else {
            $application = new HttpApplication(
                $container,
                $container->get(AppConfig::class),
            );
        }

        return $application;
    }
}
