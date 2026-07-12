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
        // Manually looping over the connection singletons so that we can check whether they still have an active transaction
        if ($this->container instanceof GenericContainer) {
            $connections = $this->container->getSingletons(Connection::class);

            foreach ($connections as $connection) {
                if (! $connection instanceof Connection) {
                    continue;
                }

                if ($connection->inTransaction()) {
                    throw new CouldNotResetConnection("There's still an active transaction, make sure to close it before ending the request");
                }
            }
        }

        $this->container->unregister(Connection::class, tagged: true);
    }
}
