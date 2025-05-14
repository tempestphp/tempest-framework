<?php

namespace Integration\Database\Mappers;

use Tests\Tempest\Fixtures\Migrations\CreateIsbnTable;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tests\Tempest\Fixtures\Migrations\CreateAuthorTable;
use Tests\Tempest\Fixtures\Migrations\CreateBookTable;
use Tests\Tempest\Fixtures\Migrations\CreateChapterTable;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use function Tempest\Database\query;

final class DatabaseModelMapperTest extends FrameworkIntegrationTestCase
{
    public function test_map(): void
    {
        $this->seed();

        $query = query('books')
            ->select(
                'books.title',
                'authors.name',
            )
            ->build()
        ;
    }

    private function seed(): void
    {
        $this->migrate(
            CreateMigrationsTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
            CreateChapterTable::class,
            CreateIsbnTable::class,
        );

        query('authors')->insert(
            ['name' => 'Brent'],
            ['name' => 'Tolkien'],
        )->execute();

        query('books')->insert(
            ['title' => 'LOTR 1', 'author_id' => 2],
            ['title' => 'LOTR 2', 'author_id' => 2],
            ['title' => 'LOTR 3', 'author_id' => 2],
            ['title' => 'Timeline Taxi', 'author_id' => 1],
        )->execute();

        query('isbns')->insert(
            ['value' => 'lotr-1', 'book_id' => 1],
            ['value' => 'lotr-2', 'book_id' => 2],
            ['value' => 'lotr-3', 'book_id' => 3],
            ['value' => 'tt', 'book_id' => 4],
        )->execute();

        query('chapters')->insert(
            ['title' => 'LOTR 1.1', 'book_id' => 1],
            ['title' => 'LOTR 1.2', 'book_id' => 1],
            ['title' => 'LOTR 1.3', 'book_id' => 1],
            ['title' => 'LOTR 2.1', 'book_id' => 2],
            ['title' => 'LOTR 2.2', 'book_id' => 2],
            ['title' => 'LOTR 2.3', 'book_id' => 2],
            ['title' => 'LOTR 3.1', 'book_id' => 3],
            ['title' => 'LOTR 3.2', 'book_id' => 3],
            ['title' => 'LOTR 3.3', 'book_id' => 3],
            ['title' => 'Timeline Taxi Chapter 1', 'book_id' => 4],
            ['title' => 'Timeline Taxi Chapter 2', 'book_id' => 4],
            ['title' => 'Timeline Taxi Chapter 3', 'book_id' => 4],
            ['title' => 'Timeline Taxi Chapter 4', 'book_id' => 4],
        )->execute();
    }
}