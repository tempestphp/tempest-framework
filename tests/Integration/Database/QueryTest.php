<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database;

use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\Query;
use Tests\Tempest\Fixtures\Migrations\CreateAuthorTable;
use Tests\Tempest\Fixtures\Migrations\CreatePublishersTable;
use Tests\Tempest\Fixtures\Modules\Books\Models\Author;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class QueryTest extends FrameworkIntegrationTestCase
{
    public function test_with_bindings(): void
    {
        $this->migrate(CreateMigrationsTable::class, CreatePublishersTable::class, CreateAuthorTable::class);

        new Author(name: 'A')->save();
        new Author(name: 'B')->save();

        $this->assertCount(1, new Query('SELECT * FROM authors WHERE name = ?')->fetch('A'));
        $this->assertCount(1, new Query('SELECT * FROM authors WHERE name = :name')->fetch(name: 'A'));
        $this->assertSame('A', new Query('SELECT * FROM authors WHERE name = ?')->fetchFirst('A')['name']);
        $this->assertSame('A', new Query('SELECT * FROM authors WHERE name = :name')->fetchFirst(name: 'A')['name']);

        new Query('DELETE FROM authors WHERE name = :name')->execute(name: 'A');
        $this->assertCount(0, new Query('SELECT * FROM authors WHERE name = ?')->fetch('A'));
    }
}
