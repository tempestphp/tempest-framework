<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\ORM\Migrations;

use Tempest\Database\DatabaseDriver;
use Tempest\Database\Migration;
use Tempest\Database\Query;

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
        return $this->driver->dialect()
            ->createQueryStatement('C')
            ->createTable()
            ->primary()
            ->createColumn('name', 'TEXT')
            ->toQuery();
    }

    public function down(): Query|null
    {
        return $this->driver->dialect()
            ->createQueryStatement('C')
            ->dropTable()
            ->toQuery();
    }
}
