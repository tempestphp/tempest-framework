<?php

declare(strict_types=1);

namespace Tempest\Database;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

final class DatabaseDriverInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): DatabaseDriver
    {
        return $container->get(DatabaseConfig::class)->driver;
    }
}
