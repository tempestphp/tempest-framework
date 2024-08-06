<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\ORM;

use Tempest\Database\DatabaseDriver;
use Tempest\Database\Migration;
use Tempest\Database\Query;
use Tempest\Database\QueryStatement;

final readonly class FooMigration implements Migration
{
    public function __construct(
        private DatabaseDriver $driver,
    ) {
    }

    public function getName(): string
    {
        return 'foo';
    }

    public function up(): Query|null
    {
        return QueryStatement::new($this->driver, table: 'Foo')
            ->create(
                fn (QueryStatement $statement) => $statement
                    ->primary()
                    ->statement('bar TEXT')
            )
            ->toQuery();
    }

    public function down(): Query|null
    {
        return null;
    }
}
