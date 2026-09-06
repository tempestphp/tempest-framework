<?php

namespace Tempest\Router;

use Tempest\Container\Container;
use Tempest\Container\Resettable;

final readonly class RouterReset implements Resettable
{
    public function __construct(
        private Container $container,
    ) {}

    public function reset(): void
    {
        $this->container->unregister(MatchedRoute::class);
    }
}
