<?php

declare(strict_types=1);

namespace Tempest\Database\Connection;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Database\Config\DatabaseConfig;

final class ConnectionInitializer implements Initializer
{
    /** @var Connection[] */
    private static array $connections = [];

    #[Singleton]
    public function initialize(Container $container): Connection
    {
        $config = $container->get(DatabaseConfig::class);
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
