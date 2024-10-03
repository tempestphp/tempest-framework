<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Discovery;

use Tempest\Core\DoNotDiscover;
use Tempest\Database\Migration;
use Tempest\Database\MigrationDiscovery;
use Tempest\Database\QueryStatement;

#[DoNotDiscover(except: [MigrationDiscovery::class])]
final class HiddenMigratableMigration implements Migration
{
    public function getName(): string
    {
        return 'hidden-migratable-migration';
    }

    public function up(): ?QueryStatement
    {
        return null;
    }

    public function down(): ?QueryStatement
    {
        return null;
    }
}
