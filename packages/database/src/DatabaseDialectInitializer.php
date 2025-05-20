<?php

namespace Tempest\Database;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Database\Config\DatabaseConfig;
use Tempest\Database\Config\DatabaseDialect;

final class DatabaseDialectInitializer implements Initializer
{
    public function initialize(Container $container): DatabaseDialect
    {
        return $container->get(DatabaseConfig::class)->dialect;
    }
}