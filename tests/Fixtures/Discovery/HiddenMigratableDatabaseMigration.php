<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Discovery;

use Tempest\Core\DoNotDiscover;
use Tempest\Database\DatabaseMigration;
use Tempest\Database\MigrationDiscovery;
use Tempest\Database\QueryStatement;

#[DoNotDiscover(except: [MigrationDiscovery::class])]
final class HiddenMigratableDatabaseMigration implements DatabaseMigration
{
    private(set) public string $name = 'hidden-migratable-migration';

    public function up(): ?QueryStatement
    {
        return null;
    }

    public function down(): ?QueryStatement
    {
        return null;
    }
}
