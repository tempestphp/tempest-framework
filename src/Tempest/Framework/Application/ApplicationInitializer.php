<?php

declare(strict_types=1);

namespace Tempest\Framework\Application;

use Tempest\Console\ConsoleApplication;
use Tempest\Console\Input\ConsoleArgumentBag;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Core\AppConfig;
use Tempest\Core\Application;
use Tempest\Http\HttpApplication;

final readonly class ApplicationInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): Application
    {
        if (PHP_SAPI === 'cli') {
            return new ConsoleApplication(
                container: $container,
                appConfig: $container->get(AppConfig::class),
                argumentBag: new ConsoleArgumentBag(['argv']),
            );
        }

        return new HttpApplication(
            container: $container,
        );
    }
}
