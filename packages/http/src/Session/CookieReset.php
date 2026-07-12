<?php

namespace Tempest\Http\Session;

use Tempest\Container\Container;
use Tempest\Container\Resettable;
use Tempest\Http\Cookie\CookieManager;

final readonly class CookieReset implements Resettable
{
    public function __construct(
        private Container $container,
    ) {}

    public function reset(): void
    {
        $this->container->unregister(CookieManager::class);
    }
}
