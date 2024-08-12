<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\ORM\Migrations;

use Tempest\Database\DatabaseDriver;
use Tempest\Database\Migration;
use Tempest\Database\Query;

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
        return $this->driver->dialect()
            ->createQueryStatement('B')
            ->createTable()
            ->primary()
            ->createColumn('c_id', 'INTEGER')
            ->toQuery();
    }

    public function down(): Query|null
    {
        return $this->driver->dialect()
            ->createQueryStatement('B')
            ->dropTable()
            ->toQuery();
    }
}
