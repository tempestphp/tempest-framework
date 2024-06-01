<?php

declare(strict_types=1);

namespace Tempest\Database\Transactions;

use PDO;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

#[Singleton]
final readonly class TransactionManagerInitializer implements Initializer
{
    public function initialize(Container $container): TransactionManager
    {
        return new GenericTransactionManager($container->get(PDO::class));
    }
}
