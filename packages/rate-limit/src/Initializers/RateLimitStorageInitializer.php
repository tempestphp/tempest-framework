<?php

declare(strict_types=1);

namespace Tempest\RateLimit\Initializers;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\RateLimit\Config\RateLimitConfig;
use Tempest\RateLimit\RateLimitStorage;

final readonly class RateLimitStorageInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): RateLimitStorage
    {
        return $container->get($container->get(RateLimitConfig::class)->storageClass);
    }
}
