<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\ORM\Migrations;

use Tempest\Database\Migration;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;

final readonly class CreateHasManyParentTable implements Migration
{
    public function getName(): string
    {
        return '100-create-has-many-parent';
    }

    public function up(): QueryStatement|null
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
