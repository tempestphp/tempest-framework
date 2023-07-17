<?php

namespace Tests\Tempest\ORM\Mappers;

use App\Migrations\CreateAuthorTable;
use App\Modules\Books\Author;
use App\Modules\Books\Book;
use Tempest\Database\Id;
use Tempest\Database\Query;
use Tests\Tempest\TestCase;

class QueryMapperTest extends TestCase
{
    /** @test */
    public function create_query()
    {
        $author = Author::new(name: 'test');

        $query = make(Query::class)->from($author);

        $table = Author::table();

        $this->assertSame("INSERT INTO {$table} (name) VALUES (:name);", $query->query);
        $this->assertSame(['name' => 'test'], $query->bindings);
    }
    
    /** @test */
    public function create_query_with_nested_relation()
    {
        $book = Book::new(
            title: 'test',
            author: Author::new(
                name: 'test'
            )
        );

        $query = make(Query::class)->from($book);

        $bookTable = Book::table();
        $authorTable = Author::table();

        $this->assertSame("INSERT INTO {$table} (name) VALUES (:name);", $query->query);
        $this->assertSame(['name' => 'test'], $query->bindings);
    }

    /** @test */
    public function update_query()
    {
        $author = Author::new(id: new Id(1), name: 'other');

        $query = make(Query::class)->from($author);

        $table = Author::table();

        $this->assertSame("UPDATE {$table} SET name = :name WHERE id = 1;", $query->query);
        $this->assertSame(['name' => 'other'], $query->bindings);
    }
}
