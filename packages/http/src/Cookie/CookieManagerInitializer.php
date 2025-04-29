<?php

declare(strict_types=1);

namespace Tempest\Http\Cookie;

use Tempest\Clock\Clock;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

final readonly class CookieManagerInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): CookieManager
    {
        $cookieManager = new CookieManager(
            clock: $container->get(Clock::class),
        );

        foreach ($_COOKIE as $key => $value) {
            $cookieManager->add(new Cookie($key, $value));
        }

        return $cookieManager;
    }
}
