<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Discovery;

use Tempest\Database\MigratesUp;
use Tempest\Database\MigrationDiscovery;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Discovery\SkipDiscovery;

#[SkipDiscovery(except: [MigrationDiscovery::class])]
final class HiddenMigratableDatabaseMigration implements MigratesUp
{
    private(set) string $name = 'hidden-migratable-migration';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('hidden')
            ->primary();
    }
}
