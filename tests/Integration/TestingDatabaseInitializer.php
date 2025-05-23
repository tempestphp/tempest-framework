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
use UnitEnum;

final class TestingDatabaseInitializer implements DynamicInitializer
{
    /** @var Connection[] */
    private static array $connections = [];

    public function canInitialize(ClassReflector $class, null|string|UnitEnum $tag): bool
    {
        return $class->getType()->matches(Database::class);
    }

    #[Singleton]
    public function initialize(ClassReflector $class, null|string|UnitEnum $tag, Container $container): Database
    {
        $tag = match (true) {
            $tag instanceof UnitEnum => $tag->name,
            is_string($tag) => $tag,
            default => '',
        };

        /** @var PDOConnection|null $connection */
        $connection = self::$connections[$tag] ?? null;

        if ($connection === null) {
            $config = $container->get(DatabaseConfig::class, $tag);
            $connection = new PDOConnection($config);
            $connection->connect();

            self::$connections[$tag] = $connection;
        }

        if ($connection->ping() === false) {
            $connection->reconnect();
        }

        $container->singleton(Connection::class, $connection, $tag);

        return new GenericDatabase(
            $connection,
            new GenericTransactionManager($connection),
        );
    }
}
