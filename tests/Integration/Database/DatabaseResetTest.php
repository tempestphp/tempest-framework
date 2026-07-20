<?php

namespace Tests\Tempest\Integration\Database;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Database\Config\SQLiteConfig;
use Tempest\Database\Database;
use Tempest\Database\Exceptions\CouldNotResetConnection;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class DatabaseResetTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function active_transaction_prevents_database_from_being_reset(): void
    {
        $this->container->config(new SQLiteConfig(
            path: __DIR__ . '/db-main.sqlite',
            tag: 'sqlite-main',
        ));

        /** @var \Tempest\Database\GenericDatabase $database */
        $database = $this->container->get(Database::class, 'sqlite-main');

        $database->connection->beginTransaction();

        $this->assertException(CouldNotResetConnection::class, function () {
            $this->container->reset();
        });

        $database->connection->close();
    }

    #[Test]
    public function properly_closed_transaction_allows_database_reset(): void
    {
        $this->container->config(new SQLiteConfig(
            path: __DIR__ . '/db-main.sqlite',
            tag: 'sqlite-main',
        ));

        /** @var \Tempest\Database\GenericDatabase $database */
        $database = $this->container->get(Database::class, 'sqlite-main');

        $database->connection->beginTransaction();
        $database->connection->close();

        $this->container->reset();

        $new = $this->container->get(Database::class, 'sqlite-main');

        $this->assertNotSame($database, $new);
    }
}
