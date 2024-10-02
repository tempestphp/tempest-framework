<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\ORM\Migrations;

use Tempest\Database\Migration;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;

final readonly class CreateHasManyThroughTable implements Migration
{
    public function getName(): string
    {
        return '100-create-has-many-through';
    }

    public function up(): QueryStatement|null
    {
        return (new CreateTableStatement('through'))
            ->primary()
            ->belongsTo('through.parent_id', 'parent.id')
            ->belongsTo('through.child_id', 'child.id');
    }

    public function down(): QueryStatement|null
    {
        return null;
    }
}
