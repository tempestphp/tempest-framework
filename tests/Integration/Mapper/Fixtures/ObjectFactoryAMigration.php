<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

use Tempest\Database\Migration;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;
use Tempest\Database\QueryStatements\PrimaryKeyStatement;
use Tempest\Database\QueryStatements\TextStatement;

class ObjectFactoryAMigration implements Migration
{
    public function getName(): string
    {
        return 'object-a';
    }

    public function up(): CreateTableStatement|null
    {
        return new CreateTableStatement(
            'ObjectFactoryA',
            [
                new PrimaryKeyStatement(),
                new TextStatement('prop'),
            ]
        );
    }

    public function down(): DropTableStatement|null
    {
        return null;
    }
}
