<?php

namespace Tests\Tempest\Integration\Database\Connection;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Database\Connection\Connection;
use Tempest\Database\Exceptions\CouldNotResetConnection;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class ConnectionResetTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function test_open_transaction_prevents_connection_from_being_reset(): void
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
    public function test_properly_closed_transaction_can_reset_connection(): void
    {
        /** @var Connection $connection */
        $connection = $this->container->get(Connection::class);

        $connection->beginTransaction();
        $connection->commit();

        $this->container->reset();

        $newConnection = $this->container->get(Connection::class);
        $this->assertNotSame($connection, $newConnection);
    }
}
