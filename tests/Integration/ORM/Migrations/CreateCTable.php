<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\ORM\Migrations;

use Tempest\Database\DatabaseMigration;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;
use Tempest\Database\QueryStatements\PrimaryKeyStatement;
use Tempest\Database\QueryStatements\TextStatement;

final class CreateCTable implements DatabaseMigration
{
    private(set) public string $name = '100-create-c';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('c', [
            new PrimaryKeyStatement(),
            new TextStatement('name'),
        ]);
    }

    public function down(): QueryStatement
    {
        return new DropTableStatement('c');
    }
}
