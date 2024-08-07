<?php

declare(strict_types=1);

namespace Tempest\Database\Migrations;

use Tempest\Database\DatabaseDriver;
use Tempest\Database\Migration;
use Tempest\Database\Query;
use Tempest\Database\QueryStatement;

final readonly class CreateMigrationsTable implements Migration
{
    public function __construct(
        private DatabaseDriver $driver,
    ) {
    }

    public function getName(): string
    {
        return '0000-00-00_create_migrations_table';
    }

    public function up(): Query|null
    {
        return $this->driver->createQueryStatement('Migration')
            ->create(function (QueryStatement $statement): QueryStatement {
                return $statement
                    ->primary()
                    ->statement('name VARCHAR(255) NOT NULL');
            })
            ->toQuery();
    }

    public function down(): Query|null
    {
        return new Query('DROP TABLE IF EXISTS Migration');
    }
}
