<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\ORM\Migrations;

use Tempest\Database\Migration;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;

final readonly class CreateHasManyChildTable implements Migration
{
    public function getName(): string
    {
        return '100-create-has-many-child';
    }

    public function up(): QueryStatement|null
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
