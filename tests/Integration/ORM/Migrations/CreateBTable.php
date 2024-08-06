<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\ORM\Migrations;

use Tempest\Database\DatabaseDriver;
use Tempest\Database\Migration;
use Tempest\Database\Query;
use Tempest\Database\QueryStatement;

final readonly class CreateBTable implements Migration
{
    public function __construct(
        private DatabaseDriver $driver,
    ) {
    }

    public function getName(): string
    {
        return '100-create-b';
    }

    public function up(): Query|null
    {
        return QueryStatement::new($this->driver, table: 'B')
            ->create(
                fn (QueryStatement $statement) => $statement
                    ->primary()
                    ->statement('c_id INTEGER')
            )
            ->toQuery();
    }

    public function down(): Query|null
    {
        return new Query("DROP TABLE B");
    }
}
