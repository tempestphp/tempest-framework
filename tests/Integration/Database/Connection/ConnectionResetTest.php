<?php

namespace Tests\Tempest\Integration\Database\Connection;

use Exception;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Database\Connection\Connection;
use Tempest\Database\Exceptions\CouldNotResetConnection;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class ConnectionResetTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function open_transaction_prevents_connection_from_being_reset(): void
    {
        /** @var Connection $connection */
        $connection = $this->container->get(Connection::class);

        $connection->beginTransaction();

        $this->assertException(CouldNotResetConnection::class, function () {
            $this->container->reset();
        });

        $connection->close();
    }

    #[Test]
    public function properly_closed_transaction_can_reset_connection(): void
    {
        /** @var Connection $connection */
        $connection = $this->container->get(Connection::class);

        $connection->beginTransaction();
        $connection->commit();

        $this->container->reset();

        $newConnection = $this->container->get(Connection::class);
        $this->assertNotSame($connection, $newConnection);
    }

    #[Test]
    public function reset_with_uninstantiated_singletons(): void
    {
        $this->container->singleton(
            Connection::class,
            fn () => throw new Exception('Should not happen'),
            tag: 'other',
        );

        /** @var Connection $connection */
        $connection = $this->container->get(Connection::class);

        $this->container->reset();

        $newConnection = $this->container->get(Connection::class);
        $this->assertNotSame($connection, $newConnection);
    }
}
