<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Migrations;

use Tempest\Database\DatabaseDriver;
use Tempest\Database\Migration;
use Tempest\Database\Query;
use Tempest\Database\QueryStatement;

final readonly class CreateAuthorTable implements Migration
{
    public function __construct(
        private DatabaseDriver $driver,
    ) {
    }

    public function getName(): string
    {
        return '0000-00-00_create_author_table';
    }

    public function up(): Query|null
    {
        return QueryStatement::new($this->driver, table: 'Author')
        ->create(
            fn (QueryStatement $statement) => $statement
                ->primary()
                ->statement('name TEXT NOT NULL')
                ->statement('type TEXT')
        )
        ->toQuery();
    }

    public function down(): Query|null
    {
        return new Query("DROP TABLE Author");
    }
}
