<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\ORM\Migrations;

use Tempest\Database\DatabaseMigration;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;

final class CreateHasManyParentTable implements DatabaseMigration
{
    private(set) public string $name = '100-create-has-many-parent';

    public function up(): QueryStatement
    {
        return (new CreateTableStatement('parent'))
            ->primary()
            ->varchar('name');
    }

    public function down(): QueryStatement|null
    {
        return null;
    }
}
