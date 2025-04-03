<?php

declare(strict_types=1);

namespace Tempest\Database;

use Tempest\Container\AllowDynamicTags;
use Tempest\Container\Container;
use Tempest\Container\CurrentTag;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Database\Connection\Connection;
use Tempest\Database\Transactions\TransactionManager;

#[AllowDynamicTags]
final class DatabaseInitializer implements Initializer
{
    #[CurrentTag]
    private ?string $tag; // @phpstan-ignore-line this is injected

    #[Singleton]
    public function initialize(Container $container): Database
    {
        return new GenericDatabase(
            $container->get(Connection::class, $this->tag),
            $container->get(TransactionManager::class, $this->tag),
        );
    }
}
