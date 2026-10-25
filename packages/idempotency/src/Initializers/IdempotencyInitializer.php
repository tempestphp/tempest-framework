<?php

declare(strict_types=1);

namespace Tempest\Idempotency\Initializers;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Idempotency\IdempotencyConfig;
use Tempest\Idempotency\IdempotencyStore;

final readonly class IdempotencyInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): IdempotencyStore
    {
        $storeClass = $container->get(IdempotencyConfig::class)->storeClass;

        return $container->get($storeClass);
    }
}
