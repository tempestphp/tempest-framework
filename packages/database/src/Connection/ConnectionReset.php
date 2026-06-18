<?php

namespace Tempest\Database\Connection;

use Tempest\Container\Container;
use Tempest\Container\GenericContainer;
use Tempest\Container\Resettable;
use Tempest\Database\Exceptions\CouldNotResetConnection;

final readonly class ConnectionReset implements Resettable
{
    public function __construct(
        private Container $container,
    ) {}

    public function reset(): void
    {
        if ($this->container instanceof GenericContainer) {
            $connections = $this->container->getSingletons(Connection::class);

            foreach ($connections as $connection) {
                if ($connection instanceof PDOConnection && $connection->inTransaction()) {
                    throw new CouldNotResetConnection("There's still an active transaction, make sure to close it before ending the request");
                }
            }
        }

        $this->container->unregister(Connection::class, tagged: true);
    }
}
