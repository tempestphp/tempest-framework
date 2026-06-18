<?php

declare(strict_types=1);

namespace Tempest\Database\Connection;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Database\Config\DatabaseConfig;

final class ConnectionInitializer implements Initializer
{
    private static ?Connection $connection = null;

    #[Singleton]
    public function initialize(Container $container): Connection
    {
        $config = $container->get(DatabaseConfig::class);

        $connection = $config->usePersistentConnection
            ? self::$connection
            : null;

        if (! $connection instanceof Connection) {
            $connection = new PDOConnection($config);
            $connection->connect();
            self::$connection = $connection;
        } elseif ($connection instanceof PDOConnection && $connection->ping() === false) {
            $connection->reconnect();
        }

        return $connection;
    }
}
