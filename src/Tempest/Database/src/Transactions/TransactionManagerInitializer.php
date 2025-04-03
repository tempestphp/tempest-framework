<?php

declare(strict_types=1);

namespace Tempest\Database\Transactions;

use Tempest\Container\AllowDynamicTags;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Container\TagName;
use Tempest\Database\Connection\Connection;

#[AllowDynamicTags]
final class TransactionManagerInitializer implements Initializer
{
    #[TagName]
    private ?string $tag; // @phpstan-ignore-line this is injected

    #[Singleton]
    public function initialize(Container $container): TransactionManager
    {
        return new GenericTransactionManager($container->get(Connection::class, $this->tag));
    }
}
