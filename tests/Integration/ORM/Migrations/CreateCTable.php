<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\ORM\Migrations;

use Tempest\Database\DatabaseDriver;
use Tempest\Database\Migration;
use Tempest\Database\Query;
use Tempest\Database\QueryStatement;

final readonly class CreateCTable implements Migration
{
    public function __construct(
        private DatabaseDriver $driver,
    ) {
    }

    public function getName(): string
    {
        return '100-create-c';
    }

    public function up(): Query|null
    {
        return QueryStatement::new($this->driver, table: 'C')
            ->create(
                fn (QueryStatement $statement) => $statement
                    ->primary()
                    ->statement('name TEXT NOT NULL')
            )
            ->toQuery();
    }

    public function down(): Query|null
    {
        return new Query("DROP TABLE C");
    }
}
