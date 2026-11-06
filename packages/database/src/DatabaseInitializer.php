<?php

declare(strict_types=1);

namespace Tempest\Database;

use Tempest\Container\Container;
use Tempest\Container\DynamicInitializer;
use Tempest\Container\Singleton;
use Tempest\Database\Config\DatabaseConfig;
use Tempest\Database\Connection\Connection;
use Tempest\Database\Transactions\GenericTransactionManager;
use Tempest\EventBus\EventBus;
use Tempest\Mapper\SerializerFactory;
use Tempest\Reflection\ClassReflector;
use UnitEnum;

final class DatabaseInitializer implements DynamicInitializer
{
    public function canInitialize(ClassReflector $class, string|UnitEnum|null $tag): bool
    {
        return $class->getType()->matches(Database::class);
    }

    #[Singleton]
    public function initialize(ClassReflector $class, string|UnitEnum|null $tag, Container $container): Database
    {
        $config = $container->get(DatabaseConfig::class, $tag);
        $connection = $container->get(Connection::class, $tag);

        return new GenericDatabase(
            connection: $connection,
            transactionManager: new GenericTransactionManager($connection),
            serializerFactory: $container->get(SerializerFactory::class),
            eventBus: $container->get(EventBus::class),
            config: $config,
        );
    }
}
