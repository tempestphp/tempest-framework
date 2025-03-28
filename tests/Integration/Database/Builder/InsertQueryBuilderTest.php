<?php

namespace Tests\Tempest\Integration\Database\Builder;

use Tempest\Database\Exceptions\CannotInsertHasManyRelation;
use Tempest\Database\Exceptions\CannotUpdateHasManyRelation;
use Tempest\Database\Id;
use Tempest\Database\Query;
use Tests\Tempest\Fixtures\Modules\Books\Models\Author;
use Tests\Tempest\Fixtures\Modules\Books\Models\AuthorType;
use Tests\Tempest\Fixtures\Modules\Books\Models\Book;
use Tests\Tempest\Fixtures\Modules\Books\Models\Chapter;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\query;

final class InsertQueryBuilderTest extends FrameworkIntegrationTestCase
{
    public function test_insert_on_plain_table(): void
    {
        $query = query('chapters')
            ->insert(
                title: 'Chapter 01',
                index: 1,
            )
            ->build();

        $this->assertSame(
            <<<SQL
            INSERT INTO `chapters` (`title`, `index`)
            VALUES (?, ?)
            SQL,
            $query->getSql(),
        );

        $this->assertSame(
            ['Chapter 01', 1],
            $query->bindings,
        );
    }

    public function test_insert_with_batch(): void
    {
        $arrayOfStuff = [
            ['chapter' => 'Chapter 01', 'index' => 1],
            ['chapter' => 'Chapter 02', 'index' => 2],
            ['chapter' => 'Chapter 03', 'index' => 3],
        ];

        $query = query('chapters')
            ->insert(...$arrayOfStuff)
            ->build();

        $this->assertSame(
            <<<SQL
            INSERT INTO `chapters` (`chapter`, `index`)
            VALUES (?, ?), (?, ?), (?, ?)
            SQL,
            $query->getSql(),
        );

        $this->assertSame(
            ['Chapter 01', 1, 'Chapter 02', 2, 'Chapter 03', 3],
            $query->bindings,
        );
    }

    public function test_insert_on_model_table(): void
    {
        $author = new Author(
            name: 'brent',
            type: AuthorType::A,
        );

        $query = query(Author::class)
            ->insert(
                $author,
                ['name' => 'other name', 'type' => AuthorType::B->value],
            )
            ->build();

        $expected = <<<SQL
        INSERT INTO `authors` (`name`, `type`)
        VALUES (?, ?), (?, ?)
        SQL;

        $this->assertSame($expected, $query->getSql());
        $this->assertSame(['brent', 'a', 'other name', 'b'], $query->bindings);
    }

    public function test_insert_on_model_table_with_new_relation(): void
    {
        $book = Book::new(
            title: 'Timeline Taxi',
            author: Author::new(
                name: 'Brent',
            ),
        );

        $bookQuery = query(Book::class)
            ->insert(
                $book,
            )
            ->build();

        $expectedBookQuery = <<<SQL
        INSERT INTO `books` (`title`, `author_id`)
        VALUES (?, ?)
        SQL;

        $this->assertSame($expectedBookQuery, $bookQuery->getSql());
        $this->assertSame('Timeline Taxi', $bookQuery->bindings[0]);
        $this->assertInstanceOf(Query::class, $bookQuery->bindings[1]);

        $authorQuery = $bookQuery->bindings[1];

        $expectedAuthorQuery = <<<SQL
        INSERT INTO `authors` (`name`)
        VALUES (?)
        SQL;

        $this->assertSame($expectedAuthorQuery, $authorQuery->getSql());
        $this->assertSame('Brent', $authorQuery->bindings[0]);
    }

    public function test_insert_on_model_table_with_existing_relation(): void
    {
        $book = Book::new(
            title: 'Timeline Taxi',
            author: Author::new(
                id: new Id(10),
                name: 'Brent',
            ),
        );

        $bookQuery = query(Book::class)
            ->insert(
                $book,
            )
            ->build();

        $expectedBookQuery = <<<SQL
        INSERT INTO `books` (`title`, `author_id`)
        VALUES (?, ?)
        SQL;

        $this->assertSame($expectedBookQuery, $bookQuery->getSql());
        $this->assertSame('Timeline Taxi', $bookQuery->bindings[0]);
        $this->assertSame(10, $bookQuery->bindings[1]);
    }

    public function test_attach_new_has_many_relation_on_update(): void
    {
        $this->markTestSkipped('Not implemented yet');

        //        query(Book::class)
        //            ->insert(
        //                title: 'Timeline Taxi',
        //                chapters: [
        //                    Chapter::new(title: 'Chapter 01'),
        //                ],
        //            )
        //            ->build();
    }
}
