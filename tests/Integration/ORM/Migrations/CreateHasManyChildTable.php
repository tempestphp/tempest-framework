<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\ORM\Migrations;

use Tempest\Database\DatabaseMigration;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;

final readonly class CreateHasManyChildTable implements DatabaseMigration
{
    public function getName(): string
    {
        return '100-create-has-many-child';
    }

    public function up(): QueryStatement
    {
        return (new CreateTableStatement('child'))
            ->primary()
            ->varchar('name');
    }

    public function down(): QueryStatement|null
    {
        return null;
    }
}
