<?php

declare(strict_types=1);

namespace Tempest\Router\RateLimiting;

use Tempest\Cache\Cache;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

final readonly class RateLimiterInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): RateLimiter
    {
        return new CacheRateLimiter(
            cache: $container->get(Cache::class),
        );
    }
}
