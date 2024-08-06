<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

use Tempest\Database\DatabaseDriver;
use Tempest\Database\Migration;
use Tempest\Database\Query;
use Tempest\Database\QueryStatement;

final readonly class ObjectFactoryAMigration implements Migration
{
    public function __construct(
        private DatabaseDriver $driver,
    ) {
    }

    public function getName(): string
    {
        return 'object-a';
    }

    public function up(): Query|null
    {
        return QueryStatement::new($this->driver, table: 'ObjectFactoryA')
            ->create(
                fn (QueryStatement $statement) => $statement
                    ->primary()
                    ->statement('prop TEXT')
            )
            ->toQuery();
    }

    public function down(): Query|null
    {
        return null;
    }
}
