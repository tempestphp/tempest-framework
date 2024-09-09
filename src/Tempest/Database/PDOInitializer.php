<?php

declare(strict_types=1);

namespace Tempest\Database;

use PDO;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

final class PDOInitializer implements Initializer
{
    private static ?PDO $pdo = null;

    #[Singleton]
    public function initialize(Container $container): PDO
    {
        // Prevent multiple PDO connections to live on in memory while running tests
        // TODO: need to improve
        if (self::$pdo === null) {
            $databaseConfig = $container->get(DatabaseConfig::class);

            $connection = $databaseConfig->connection();

            self::$pdo = new PDO(
                $connection->getDsn(),
                $connection->getUsername(),
                $connection->getPassword(),
            );
        }

        return self::$pdo;
    }
}
