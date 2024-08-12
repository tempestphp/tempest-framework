<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\ORM;

use Tempest\Database\DatabaseDriver;
use Tempest\Database\Migration;
use Tempest\Database\Query;

readonly class FooMigration implements Migration
{
    public function __construct(
        private DatabaseDriver $driver
    ) {
    }

    public function getName(): string
    {
        return 'foo';
    }

    public function up(): Query|null
    {
        return $this->driver
            ->createQueryStatement('Foo')
            ->createTable()
            ->primary()
            ->createColumn('bar', 'TEXT')
            ->toQuery();
    }

    public function down(): Query|null
    {
        return null;
    }
}
