<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Discovery;

use Symfony\Component\Console\Application;
use Tempest\Database\MigratesUp;
use Tempest\Database\MigrationDiscovery;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Discovery\SkipDiscovery;

#[SkipDiscovery(when: static function (Application $application): bool {
    return ! $application instanceof ConsoleApplication;
})]
final class ConditionallyHiddenDatabaseMigration implements MigratesUp
{
    private(set) string $name = 'conditionally-hidden-migration';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('hidden')
            ->primary();
    }
}
