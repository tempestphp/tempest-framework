<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\Builder;

use PHPUnit\Framework\Attributes\CoversNothing;
use Tempest\Database\Builder\QueryBuilders\SelectQueryBuilder;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tests\Tempest\Fixtures\Migrations\CreateAuthorTable;
use Tests\Tempest\Fixtures\Migrations\CreateBookTable;
use Tests\Tempest\Fixtures\Modules\Books\Models\Author;
use Tests\Tempest\Fixtures\Modules\Books\Models\Book;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\query;

/**
 * @internal
 */
#[CoversNothing]
final class SelectQueryBuilderTest extends FrameworkIntegrationTestCase
{
    public function test_select_query(): void
    {
        $query = query('chapters')
            ->select('title', 'index')
            ->where('`title` = ?', 'Timeline Taxi')
            ->andWhere('`index` <> ?', '1')
            ->orWhere('`createdAt` > ?', '2025-01-01')
            ->orderBy('`index` ASC')
            ->build();

        $expected = <<<SQL
        SELECT `title`, `index`
        FROM `chapters`
        WHERE `title` = ?
        AND `index` <> ?
        OR `createdAt` > ?
        ORDER BY `index` ASC
        SQL;

        $sql = $query->getSql();
        $bindings = $query->bindings;

        $this->assertSame($expected, $sql);
        $this->assertSame(['Timeline Taxi', '1', '2025-01-01'], $bindings);
    }

    public function test_select_without_any_fields_specified(): void
    {
        $query = query('chapters')->select()->build();

        $sql = $query->getSql();

        $expected = <<<SQL
        SELECT *
        FROM `chapters`
        SQL;

        $this->assertSame($expected, $sql);
    }

    public function test_select_from_model(): void
    {
        $query = query(Author::class)->select()->build();

        $sql = $query->getSql();

        $expected = <<<SQL
        SELECT `authors`.`name` AS `authors.name`, `authors`.`type` AS `authors.type`, `authors`.`id` AS `authors.id`
        FROM `authors`
        SQL;

        $this->assertSame($expected, $sql);
    }

    public function test_where_statement(): void
    {
        $this->migrate(
            CreateMigrationsTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        Book::new(title: 'A')->save();
        Book::new(title: 'B')->save();
        Book::new(title: 'C')->save();
        Book::new(title: 'D')->save();

        $book = Book::select()->where('title = ?', 'B')->first();

        $this->assertSame('B', $book->title);
    }

    public function test_order_by(): void
    {
        $this->migrate(
            CreateMigrationsTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        Book::new(title: 'A')->save();
        Book::new(title: 'B')->save();
        Book::new(title: 'C')->save();
        Book::new(title: 'D')->save();

        $book = Book::select()->orderBy('title DESC')->first();

        $this->assertSame('D', $book->title);
    }

    public function test_limit(): void
    {
        $this->migrate(
            CreateMigrationsTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        Book::new(title: 'A')->save();
        Book::new(title: 'B')->save();
        Book::new(title: 'C')->save();
        Book::new(title: 'D')->save();

        $books = Book::select()->limit(2)->all();

        $this->assertCount(2, $books);
        $this->assertSame('A', $books[0]->title);
        $this->assertSame('B', $books[1]->title);
    }

    public function test_offset(): void
    {
        $this->migrate(
            CreateMigrationsTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        Book::new(title: 'A')->save();
        Book::new(title: 'B')->save();
        Book::new(title: 'C')->save();
        Book::new(title: 'D')->save();

        $books = Book::select()
            ->limit(2)
            ->offset(2)
            ->all();

        $this->assertCount(2, $books);
        $this->assertSame('C', $books[0]->title);
        $this->assertSame('D', $books[1]->title);
    }

    public function test_chunk(): void
    {
        $this->migrate(
            CreateMigrationsTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        Book::new(title: 'A')->save();
        Book::new(title: 'B')->save();
        Book::new(title: 'C')->save();
        Book::new(title: 'D')->save();

        $results = [];
        Book::select()->chunk(function (array $chunk) use (&$results): void {
            $results = [...$results, ...$chunk];
        }, 2);
        $this->assertCount(4, $results);

        $results = [];
        Book::select()->where('title <> "A"')->chunk(function (array $chunk) use (&$results): void {
            $results = [...$results, ...$chunk];
        }, 2);
        $this->assertCount(3, $results);
    }

    public function test_raw(): void
    {
        $this->migrate(
            CreateMigrationsTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        Book::new(title: 'A')->save();
        Book::new(title: 'B')->save();
        $books = Book::select()->raw('LIMIT 1')->all();

        $this->assertCount(1, $books);
        $this->assertSame('A', $books[0]->title);
    }

    public function test_select_query_with_conditions(): void
    {
        $query = query('chapters')
            ->select('title', 'index')
            ->when(
                true,
                fn (SelectQueryBuilder $query) => $query
                    ->where('`title` = ?', 'Timeline Taxi')
                    ->andWhere('`index` <> ?', '1')
                    ->orWhere('`createdAt` > ?', '2025-01-01'),
            )
            ->when(
                false,
                fn (SelectQueryBuilder $query) => $query
                    ->where('`title` = ?', 'Timeline Uber')
                    ->andWhere('`index` <> ?', '2')
                    ->orWhere('`createdAt` > ?', '2025-01-02'),
            )
            ->orderBy('`index` ASC')
            ->build();

        $expected = <<<SQL
        SELECT `title`, `index`
        FROM `chapters`
        WHERE `title` = ?
        AND `index` <> ?
        OR `createdAt` > ?
        ORDER BY `index` ASC
        SQL;

        $sql = $query->getSql();
        $bindings = $query->bindings;

        $this->assertSame($expected, $sql);
        $this->assertSame(['Timeline Taxi', '1', '2025-01-01'], $bindings);
    }
}
