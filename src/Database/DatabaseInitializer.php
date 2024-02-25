<?php

declare(strict_types=1);

namespace Tempest\Database;

use PDO;
use Tempest\Container\Container;
use Tempest\Container\Initializer;

final readonly class DatabaseInitializer implements Initializer
{
    public function initialize(Container $container): object
    {
        $database = new GenericDatabase($container->get(PDO::class));

        $container->singleton(Database::class, fn () => $database);

        return $database;
    }
}
