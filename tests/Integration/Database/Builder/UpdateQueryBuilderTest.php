<?php

namespace Tests\Tempest\Integration\Database\Builder;

use Tempest\Database\Exceptions\InvalidUpdateStatement;
use Tempest\Database\Id;
use Tests\Tempest\Fixtures\Modules\Books\Models\Author;
use Tests\Tempest\Fixtures\Modules\Books\Models\AuthorType;
use Tests\Tempest\Fixtures\Modules\Books\Models\Book;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\query;

final class UpdateQueryBuilderTest extends FrameworkIntegrationTestCase
{
    public function test_update_on_plain_table(): void
    {
        $query = query('chapters')
            ->update(
                title: 'Chapter 01',
                index: 1,
            )
            ->where('`id` = ?', 10)
            ->build();

        $this->assertSame(
            <<<SQL
            UPDATE `chapters`
            SET `title` = ?, `index` = ?
            WHERE `id` = ?
            SQL,
            $query->getSql(),
        );

        $this->assertSame(
            ['Chapter 01', 1, 10],
            $query->bindings,
        );
    }

    public function test_global_update(): void
    {
        $query = query('chapters')
            ->update(index: 0)
            ->allowAll()
            ->build();

        $this->assertSame(
            <<<SQL
            UPDATE `chapters`
            SET `index` = ?
            SQL,
            $query->getSql(),
        );

        $this->assertSame(
            [0],
            $query->bindings,
        );
    }

    public function test_global_update_fails_without_allow_all(): void
    {
        $this->expectException(InvalidUpdateStatement::class);

        query('chapters')
            ->update(index: 0)
            ->build()
            ->getSql();
    }

    public function test_model_update_with_values(): void
    {
        $query = query(Book::class)
            ->update(
                title: 'Chapter 02',
            )
            ->where('`id` = ?', 10)
            ->build();

        $this->assertSame(
            <<<SQL
            UPDATE `books`
            SET `title` = ?
            WHERE `id` = ?
            SQL,
            $query->getSql(),
        );

        $this->assertSame(
            ['Chapter 02', 10],
            $query->bindings,
        );
    }

    public function test_model_update_with_object(): void
    {
        $book = Book::new(
            id: new Id(10),
            title: 'Chapter 01',
        );

        $query = query($book)
            ->update(
                title: 'Chapter 02',
            )
            ->build();

        $this->assertSame(
            <<<SQL
            UPDATE `books`
            SET `title` = ?
            WHERE `id` = ?
            SQL,
            $query->getSql(),
        );

        $this->assertSame(
            ['Chapter 02', 10],
            $query->bindings,
        );
    }

    public function test_model_values_get_serialized(): void
    {
        $author = Author::new(
            id: new Id(10),
        );

        $query = query($author)
            ->update(
                type: AuthorType::A,
            )
            ->build();

        $this->assertSame(
            ['a', 10],
            $query->bindings,
        );
    }
}
