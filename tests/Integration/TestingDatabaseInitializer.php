<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration;

use Tempest\Container\Container;
use Tempest\Container\DynamicInitializer;
use Tempest\Container\Singleton;
use Tempest\Database\Config\DatabaseConfig;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\Connection\Connection;
use Tempest\Database\Connection\PDOConnection;
use Tempest\Database\Database;
use Tempest\Database\GenericDatabase;
use Tempest\Database\Transactions\GenericTransactionManager;
use Tempest\Reflection\ClassReflector;

final class TestingDatabaseInitializer implements DynamicInitializer
{
    private static ?PDOConnection $connection = null;

    public function canInitialize(ClassReflector $class, ?string $tag): bool
    {
        return $class->getType()->matches(Database::class);
    }

    #[Singleton]
    public function initialize(ClassReflector $class, ?string $tag, Container $container): Database
    {
        if (self::$connection === null) {
            $config = $container->get(DatabaseConfig::class, $tag);
            $connection = new PDOConnection($config);
            $connection->connect();

            self::$connection = $connection;
        }

        if (self::$connection->ping() === false)
        {
            self::$connection->reconnect();
        }

        return new GenericDatabase(
            self::$connection,
            new GenericTransactionManager(self::$connection),
            $container->get(DatabaseDialect::class),
        );
    }
}
