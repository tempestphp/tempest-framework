<?php

declare(strict_types=1);

namespace Tempest\Http\Cookie;

use Tempest\Clock\Clock;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Core\AppConfig;

final readonly class CookieManagerInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): CookieManager
    {
        return new CookieManager(
            appConfig: $container->get(AppConfig::class),
            clock: $container->get(Clock::class),
        );
    }
}
