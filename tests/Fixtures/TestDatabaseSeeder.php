<?php

namespace Tests\Tempest\Fixtures;

use Tempest\Database\DatabaseSeeder;
use Tempest\Discovery\SkipDiscovery;
use Tests\Tempest\Fixtures\Modules\Books\Models\Book;
use UnitEnum;

use function Tempest\Database\query;

final class TestDatabaseSeeder implements DatabaseSeeder
{
    public function run(null|string|UnitEnum $database): void
    {
        query(Book::class)
            ->insert(
                title: 'Timeline Taxi',
            )
            ->onDatabase($database)
            ->execute();
    }
}
