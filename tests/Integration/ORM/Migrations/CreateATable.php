<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\ORM\Migrations;

use Tempest\Database\DatabaseMigration;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;
use Tempest\Database\QueryStatements\PrimaryKeyStatement;
use Tempest\Database\QueryStatements\RawStatement;

final class CreateATable implements DatabaseMigration
{
    private(set) string $name = '100-create-a';

    public function up(): QueryStatement
    {
        return new CreateTableStatement(
            'a',
            [
                new PrimaryKeyStatement(),
                new RawStatement('b_id INTEGER'),
            ],
        );
    }

    public function down(): QueryStatement
    {
        return new DropTableStatement('a');
    }
}
