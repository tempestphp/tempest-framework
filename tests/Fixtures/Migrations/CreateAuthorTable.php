<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Migrations;

use Tempest\Database\DatabaseDriver;
use Tempest\Database\Migration;
use Tempest\Database\Query;

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
        return $this->driver->dialect()
            ->createQueryStatement('Author')
            ->createTable()
            ->primary()
            ->createColumn('name', 'TEXT')
            ->createColumn('type', 'TEXT', nullable: true)
            ->toQuery();
    }

    public function down(): Query|null
    {
        return $this->driver->dialect()
            ->createQueryStatement('Author')
            ->dropTable()
            ->toQuery();
    }
}
