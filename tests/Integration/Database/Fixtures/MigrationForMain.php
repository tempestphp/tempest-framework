<?php

namespace Tests\Tempest\Integration\Database\Fixtures;

use Tempest\Database\Database;
use Tempest\Database\DatabaseMigration;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;
use Tempest\Database\ShouldMigrate;

final class MigrationForMain implements DatabaseMigration, ShouldMigrate
{
    public string $name = '000_main';

    public function shouldMigrate(Database $database): bool
    {
        return $database->tag === 'main';
    }

    public function up(): QueryStatement
    {
        return new CreateTableStatement('main_table')->primary();
    }

    public function down(): QueryStatement
    {
        return new DropTableStatement('main_table');
    }
}
