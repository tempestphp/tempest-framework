<?php

declare(strict_types=1);

namespace Tests\Tempest\Mapper;

use App\Modules\Books\Author;
use App\Modules\Books\Book;
use Tempest\Database\Query;
use Tempest\Mapper\ObjectFactory;
use Tests\Tempest\TestCase;

class ObjectFactoryTest extends TestCase
{
    /** @test */
    public function test_store()
    {
        $author = new Author();

        $author->name = 'Brent';
        $author->addBooks(
            new Book('A'),
            new Book('B'),
        );
        
        $factory = $this->container->get(ObjectFactory::class);

        $query = make(Query::class)->from($author);

        $factory->persist($author)->as();
    }

    /** @test */
    public function test_map_from_sql()
    {
        $book = make(Book::class)->from(new Query("SELECT * FROM Book WHERE id = :id", [
            'id' => 1,
        ]));

        $this->assertInstanceOf(Book::class, $book);
    }
}
