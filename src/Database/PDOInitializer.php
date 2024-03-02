<?php

declare(strict_types=1);

namespace Tempest\Database;

use PDO;
use Tempest\Container\CanInitialize;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

#[Singleton]
final readonly class PDOInitializer implements Initializer, CanInitialize
{
    public function canInitialize(string $className): bool
    {
        return $className === PDO::class;
    }

    public function initialize(Container $container): PDO
    {
        $databaseConfig = $container->get(DatabaseConfig::class);

        $driver = $databaseConfig->driver;

        return new PDO(
            $driver->getDsn(),
            $driver->getUsername(),
            $driver->getPassword(),
        );
    }
}
