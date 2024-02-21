<?php

declare(strict_types=1);

namespace Tempest\Database;

use PDO;
use Tempest\Interface\Container;
use Tempest\Interface\Database;
use Tempest\Interface\Initializer;

final readonly class DatabaseInitializer implements Initializer
{
    public function initialize(string $className, Container $container): object
    {
        $database = new GenericDatabase($container->get(PDO::class));

        $container->singleton(Database::class, fn () => $database);

        return $database;
    }
}
