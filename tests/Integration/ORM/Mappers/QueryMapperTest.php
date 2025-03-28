<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\ORM\Mappers;

use Tempest\Database\Builder\QueryBuilders\UpdateQueryBuilder;
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

        $this->assertSame(<<<'SQL'
        INSERT INTO `authors` (`name`)
        VALUES (?)
        SQL, $query->getSql());
        $this->assertSame(['test'], $query->bindings);
    }

    public function test_update_query(): void
    {
        $author = Author::new(id: new Id(1), name: 'original');

        $query = query($author)->update(name: 'other')->build();

        $this->assertSame(<<<'SQL'
        UPDATE `authors`
        SET `name` = ?
        WHERE `id` = ?
        SQL, $query->getSql());

        $this->assertSame(['other', 1], $query->bindings);
    }
}
