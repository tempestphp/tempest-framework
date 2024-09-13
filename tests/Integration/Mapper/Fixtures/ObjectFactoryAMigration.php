<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

use Tempest\Database\Migration;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\PrimaryKeyStatement;
use Tempest\Database\QueryStatements\TextStatement;

final class ObjectFactoryAMigration implements Migration
{
    public function getName(): string
    {
        return 'object-a';
    }

    public function up(): QueryStatement|null
    {
        return new CreateTableStatement(
            'ObjectFactoryA',
            [
                new PrimaryKeyStatement(),
                new TextStatement('prop'),
            ]
        );
    }

    public function down(): QueryStatement|null
    {
        return null;
    }
}
