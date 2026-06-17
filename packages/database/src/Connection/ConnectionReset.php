<?php

namespace Tempest\Database\Connection;

use Tempest\Container\Container;
use Tempest\Container\Resettable;

final readonly class ConnectionReset implements Resettable
{
    public function __construct(
        private Container $container,
    ) {}

    public function reset(): void
    {
        $this->container->unregister(Connection::class);
    }
}
