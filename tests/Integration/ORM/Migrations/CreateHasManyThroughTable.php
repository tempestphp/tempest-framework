<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\ORM\Migrations;

use Tempest\Database\DatabaseMigration;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;

final class CreateHasManyThroughTable implements DatabaseMigration
{
    private(set) string $name = '100-create-has-many-through';

    public function up(): QueryStatement
    {
        return (new CreateTableStatement('through'))
            ->primary()
            ->belongsTo('through.parent_id', 'parent.id')
            ->belongsTo('through.child_id', 'child.id')
            ->belongsTo('through.child2_id', 'child.id', nullable: true);
    }

    public function down(): QueryStatement|null
    {
        return null;
    }
}
