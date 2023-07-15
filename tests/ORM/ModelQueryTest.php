<?php

declare(strict_types=1);

namespace Tests\Tempest\ORM;

use App\Modules\Books\Author;
use App\Modules\Books\Book;
use PHPUnit\Framework\TestCase;
use Tempest\ORM\Direction;
use Tempest\ORM\ModelQuery;

class ModelQueryTest extends TestCase
{
    /** @test */
    public function test_build()
    {
        $query = ModelQuery::new(Author::class);

        $expected = <<<SQL
        SELECT *
        FROM Author;
        SQL;

        $this->assertSame($expected, $query->buildSelect());
    }

    /** @test */
    public function test_build_complex()
    {
        $query = ModelQuery::new(Author::class)
            ->select(Author::field('name'), Book::field('title'))
            ->from(Author::table())
            ->join(Book::table(), Book::field('author_id'), Author::field('id'))
            ->where(Author::field('name'), '"Brent"')
            ->orderBy(Author::field('name'), Direction::DESC)
            ->limit(10);

        $expected = <<<SQL
        SELECT Author.name, Book.title
        FROM Author INNER JOIN Book ON Book.author_id = Author.id
        WHERE Author.name = "Brent"
        ORDER BY Author.name DESC
        LIMIT 10;
        SQL;

        $this->assertSame($expected, $query->buildSelect());
    }
}
