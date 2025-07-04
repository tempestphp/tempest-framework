<?php

namespace Tests\Tempest\Fixtures;

use Tempest\Database\DatabaseSeeder;
use Tests\Tempest\Fixtures\Modules\Books\Models\Book;
use UnitEnum;

use function Tempest\Database\query;

final class SecondTestDatabaseSeeder implements DatabaseSeeder
{
    public function run(null|string|UnitEnum $database): void
    {
        query(Book::class)
            ->insert(
                title: 'Timeline Taxi 2',
            )
            ->onDatabase($database)
            ->execute();
    }
}
