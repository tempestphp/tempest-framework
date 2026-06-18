<?php

namespace Tempest\Database;

use Tempest\Container\Container;
use Tempest\Container\Resettable;

final readonly class DatabaseReset implements Resettable
{
    public function __construct(
        private Container $container,
    ) {}

    public function reset(): void
    {
        $this->container->unregister(Database::class, tagged: true);
    }
}
