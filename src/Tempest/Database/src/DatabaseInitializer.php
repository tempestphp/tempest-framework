<?php

declare(strict_types=1);

namespace Tempest\Database;

use Tempest\Container\Container;
use Tempest\Container\DynamicInitializer;
use Tempest\Container\Singleton;
use Tempest\Database\Config\DatabaseConfig;
use Tempest\Database\Connection\PDOConnection;
use Tempest\Database\Transactions\GenericTransactionManager;
use Tempest\Reflection\ClassReflector;

final readonly class DatabaseInitializer implements DynamicInitializer
{
    public function canInitialize(ClassReflector $class, ?string $tag): bool
    {
        return $class->getType()->matches(Database::class);
    }

    #[Singleton]
    public function initialize(ClassReflector $class, ?string $tag, Container $container): Database
    {
        $config = $container->get(DatabaseConfig::class, $tag);

        $connection = new PDOConnection($config);

        return new GenericDatabase(
            $connection,
            new GenericTransactionManager($connection),
        );
    }
}
