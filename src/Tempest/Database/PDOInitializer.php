<?php

declare(strict_types=1);

namespace Tempest\Database;

use PDO;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

final readonly class PDOInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): PDO
    {
        $databaseConfig = $container->get(DatabaseConfig::class);

        $connection = $databaseConfig->connection();

        return new PDO(
            $connection->getDsn(),
            $connection->getUsername(),
            $connection->getPassword(),
        );
    }
}
