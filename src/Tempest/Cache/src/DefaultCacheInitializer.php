<?php

declare(strict_types=1);

namespace Tempest\Cache;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

final readonly class DefaultCacheInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): Cache
    {
        return new Cache($container->get(CacheConfig::class)->pool);
    }
}
