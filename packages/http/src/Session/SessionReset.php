<?php

namespace Tempest\Http\Session;

use Tempest\Container\Container;
use Tempest\Container\Resettable;

final readonly class SessionReset implements Resettable
{
    public function __construct(
        private Container $container,
    ) {}

    public function reset(): void
    {
        $this->container->unregister(Session::class);
    }
}
