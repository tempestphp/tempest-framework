<?php

declare(strict_types=1);

namespace Tempest\Database\Connection;

use Tempest\Container\Container;
use Tempest\Container\DynamicInitializer;
use Tempest\Container\Singleton;
use Tempest\Database\Config\DatabaseConfig;
use Tempest\Reflection\ClassReflector;
use UnitEnum;

final class ConnectionInitializer implements DynamicInitializer
{
    /** @var Connection[] */
    private static array $connections = [];

    public function canInitialize(ClassReflector $class, string|UnitEnum|null $tag): bool
    {
        return $class->getName() === Connection::class;
    }

    #[Singleton]
    public function initialize(ClassReflector $class, string|UnitEnum|null $tag, Container $container): Connection
    {
        $config = $container->get(DatabaseConfig::class, $tag);
        $connectionKey = $this->getConnectionKey($config);

        $connection = $config->usePersistentConnection
            ? self::$connections[$connectionKey] ?? null
            : null;

        if (! $connection instanceof Connection) {
            $connection = new PDOConnection($config);
            $connection->connect();
            self::$connections[$connectionKey] = $connection;
        } elseif ($connection->ping() === false) {
            $connection->reconnect();
        }

        return $connection;
    }

    private function getConnectionKey(DatabaseConfig $config): string
    {
        return hash('xxh128', serialize([
            $config->dsn,
            $config->username,
            $config->options,
            $config->password,
            $config->tag,
        ]));
    }
}
