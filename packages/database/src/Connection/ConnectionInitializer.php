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
        $databaseConfig = $container->get(DatabaseConfig::class);

        $connection = self::$connection;

        if (! $connection instanceof Connection) {
            $connection = new PDOConnection($databaseConfig);
            $connection->connect();
            self::$connection = $connection;
        }

        if ($connection instanceof PDOConnection && $connection->ping() === false) {
            $connection->reconnect();
        }

        return $connection;
    }
}
