<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\ORM\Migrations;

use Tempest\Database\DatabaseDriver;
use Tempest\Database\Migration;
use Tempest\Database\Query;
use Tempest\Database\QueryStatement;

final readonly class CreateATable implements Migration
{
    public function __construct(
        private DatabaseDriver $driver,
    ) {
    }

    public function getName(): string
    {
        return '100-create-a';
    }

    public function up(): Query|null
    {
        return $this->driver->createQueryStatement('A')
            ->create(
                fn (QueryStatement $statement) => $statement
                    ->primary()
                    ->statement('b_id INTEGER')
            )
            ->toQuery();
    }

    public function down(): Query|null
    {
        return new Query("DROP TABLE A");
    }
}
