<?php

declare(strict_types=1);

namespace Tempest\RateLimit\Initializers;

use Tempest\Clock\Clock;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\RateLimit\GenericRateLimiter;
use Tempest\RateLimit\RateLimiter;
use Tempest\RateLimit\RateLimitStorage;

final readonly class RateLimiterInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): RateLimiter
    {
        return new GenericRateLimiter(
            storage: $container->get(RateLimitStorage::class),
            clock: $container->get(Clock::class),
        );
    }
}
