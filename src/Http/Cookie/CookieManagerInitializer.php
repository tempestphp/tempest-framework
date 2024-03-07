<?php

declare(strict_types=1);

namespace Tempest\Http\Cookie;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

#[Singleton]
final readonly class CookieManagerInitializer implements Initializer
{
    public function initialize(Container $container): CookieManager
    {
        return CookieManager::fromGlobals();
    }
}
