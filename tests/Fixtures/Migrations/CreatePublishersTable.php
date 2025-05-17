<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Migrations;

use Tempest\Database\DatabaseMigration;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;
use Tests\Tempest\Fixtures\Modules\Books\Models\Publisher;

final class CreatePublishersTable implements DatabaseMigration
{
    private(set) string $name = '0000-00-00_create_publishers_table';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(Publisher::class)
            ->primary()
            ->text('name')
            ->text('description');
    }

    public function down(): QueryStatement
    {
        return DropTableStatement::forModel(Publisher::class);
    }
}
