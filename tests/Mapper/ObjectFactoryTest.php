<?php

namespace Tests\Tempest\Mapper;

use App\Modules\Books\Book;
use Tests\Tempest\TestCase;

class ObjectFactoryTest extends TestCase
{
    /** @test */
    public function test_map_from_sql()
    {
        $book = make(Book::class)->from("SELECT * FROM Book");

        $this->assertInstanceOf(Book::class, $book);
    }
}
