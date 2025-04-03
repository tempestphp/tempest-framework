<?php

declare(strict_types=1);

namespace Tempest\Database\Connection;

use Tempest\Container\AllowDynamicTags;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Container\CurrentTag;
use Tempest\Database\Config\DatabaseConfig;

#[AllowDynamicTags]
final class ConnectionInitializer implements Initializer
{
    #[CurrentTag]
    private ?string $tag; // @phpstan-ignore-line this is injected

    #[Singleton]
    public function initialize(Container $container): Connection
    {
        $databaseConfig = $container->get(DatabaseConfig::class, $this->tag);

        $connection = new PDOConnection($databaseConfig);
        $connection->connect();

        return $connection;
    }
}
