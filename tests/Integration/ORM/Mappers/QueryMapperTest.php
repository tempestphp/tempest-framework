<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\ORM\Mappers;

use Tempest\Database\Builder\QueryBuilders\UpdateQueryBuilder;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\Database;
use Tempest\Database\Id;
use Tempest\Database\Query;
use Tests\Tempest\Fixtures\Modules\Books\Models\Author;
use Tests\Tempest\Fixtures\Modules\Books\Models\Book;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\query;

/**
 * @internal
 */
final class QueryMapperTest extends FrameworkIntegrationTestCase
{
    public function test_insert_query(): void
    {
        $author = Author::new(name: 'test');

        $query = query(Author::class)->insert($author)->build();

        $dialect = $this->container->get(Database::class)->dialect;

        $expected = match ($dialect) {
            DatabaseDialect::POSTGRESQL => <<<'SQL'
            INSERT INTO authors (name)
            VALUES (?) RETURNING *
            SQL,
            default => <<<'SQL'
            INSERT INTO `authors` (`name`)
            VALUES (?)
            SQL,
        };

        $this->assertSame($expected, $query->toSql());
        $this->assertSame(['test'], $query->bindings);
    }

    public function test_update_query(): void
    {
        $author = Author::new(id: new Id(1), name: 'original');

        $query = query($author)->update(name: 'other')->build();

        $dialect = $this->container->get(Database::class)->dialect;

        $expected = match ($dialect) {
            DatabaseDialect::POSTGRESQL => <<<'SQL'
            UPDATE authors
            SET name = ?
            WHERE id = ?
            SQL,
            default => <<<'SQL'
            UPDATE `authors`
            SET `name` = ?
            WHERE `id` = ?
            SQL,
        };

        $this->assertSame($expected, $query->toSql());

        $this->assertSame(['other', 1], $query->bindings);
    }
}
