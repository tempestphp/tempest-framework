<?php

namespace Tempest\Database;

use PDO;
use Tempest\Interfaces\CanInitialize;
use Tempest\Interfaces\Container;

final readonly class PDOInitializer implements CanInitialize
{
    public function canInitialize(string $className): bool
    {
        return $className === PDO::class;
    }

    public function initialize(string $className, Container $container): PDO
    {
        $databaseConfig = $container->get(DatabaseConfig::class);

        $driver = $databaseConfig->driver;

        $pdo = new PDO(
            $driver->getDsn(),
            $driver->getUsername(),
            $driver->getPassword(),
        );

        $container->singleton(PDO::class, fn () => $pdo);

        return $pdo;
    }
}
