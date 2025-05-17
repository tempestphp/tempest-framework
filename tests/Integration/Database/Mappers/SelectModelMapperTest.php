<?php

namespace Integration\Database\Mappers;

use Tempest\Auth\Install\User;
use Tempest\Database\Mappers\SelectModelMapper;
use Tests\Tempest\Fixtures\Modules\Books\Models\Author;
use Tests\Tempest\Fixtures\Modules\Books\Models\Book;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\map;

final class SelectModelMapperTest extends FrameworkIntegrationTestCase
{
    public function test_map(): void
    {
        $data = $this->data();

        $books = map($data)->with(SelectModelMapper::class)->to(Book::class);

        $this->assertCount(4, $books);
        $this->assertSame('LOTR 1', $books[0]->title);
        $this->assertSame('LOTR 2', $books[1]->title);
        $this->assertSame('LOTR 3', $books[2]->title);
        $this->assertSame('Timeline Taxi', $books[3]->title);

        $book = $books[0];
        $this->assertSame('Tolkien', $book->author->name);
        $this->assertCount(3, $book->chapters);

        $this->assertSame('LOTR 1.1', $book->chapters[0]->title);
        $this->assertSame('LOTR 1.2', $book->chapters[1]->title);
        $this->assertSame('LOTR 1.3', $book->chapters[2]->title);

        $this->assertSame('lotr-1', $book->isbn->value);
    }

    public function test_has_many_map(): void
    {
        $data = [
            [
                'books.id' => 1,
                'books.title' => 'LOTR',
                'chapters.id' => 1,
                'chapters.title' => 'LOTR 1.1',
            ],
            [
                'books.id' => 1,
                'chapters.id' => 2,
                'chapters.title' => 'LOTR 1.2',
            ],
            [
                'books.id' => 1,
                'chapters.id' => 3,
                'chapters.title' => 'LOTR 1.3',
            ],
        ];

        $books = map($data)->with(SelectModelMapper::class)->to(Book::class);
        $this->assertCount(3, $books[0]->chapters);
        $this->assertSame('LOTR 1.1', $books[0]->chapters[0]->title);
        $this->assertSame('LOTR 1.2', $books[0]->chapters[1]->title);
        $this->assertSame('LOTR 1.3', $books[0]->chapters[2]->title);
    }

    public function test_deeply_nested_map(): void
    {
        $data = [
            [
                'books.id' => 1,
                'books.title' => 'LOTR 1',
                'authors.name' => 'Tolkien',
                'authors.publishers.id' => 2,
                'authors.publishers.name' => 'Houghton Mifflin',
                'authors.publishers.description' => 'Hello!',
            ],
        ];

        $books = map($data)->with(SelectModelMapper::class)->to(Book::class);

        $this->assertSame('Houghton Mifflin', $books[0]->author->publisher->name);
    }

    public function test_deeply_nested_has_many_map(): void
    {
        $data = [
            [
                'authors.id' => 1,
                'books.id' => 1,
                'books.chapters.id' => 1,
                'books.chapters.title' => 'LOTR 1.1',
            ],
            [
                'authors.id' => 1,
                'books.id' => 1,
                'books.chapters.id' => 2,
                'books.chapters.title' => 'LOTR 1.2',
            ],
        ];

        $authors = map($data)->with(SelectModelMapper::class)->to(Author::class);

        $this->assertCount(2, $authors[0]->books[0]->chapters);
    }

    public function test_map_user_permissions(): void
    {
        $data = [
            [
                'users.name' => 'Brent',
                'users.email' => 'brendt@stitcher.io',
                'users.id' => 1,
                'userPermissions.user_id' => 1,
                'userPermissions.permission_id' => 1,
                'userPermissions.id' => 1,
            ],
        ];

        $users = map($data)->with(SelectModelMapper::class)->to(User::class);

        $this->assertCount(1, $users[0]->userPermissions);
    }

    private function data(): array
    {
        return [
            0 => [
                'books.id' => 1,
                'authors.id' => 2,
                'chapters.id' => 1,
                'isbns.id' => 1,
                'books.title' => 'LOTR 1',
                'authors.name' => 'Tolkien',
                'chapters.title' => 'LOTR 1.1',
                'isbns.value' => 'lotr-1',
            ],
            1 => [
                'books.id' => 1,
                'authors.id' => 2,
                'chapters.id' => 2,
                'isbns.id' => 1,
                'books.title' => 'LOTR 1',
                'authors.name' => 'Tolkien',
                'chapters.title' => 'LOTR 1.2',
                'isbns.value' => 'lotr-1',
            ],
            2 => [
                'books.id' => 1,
                'authors.id' => 2,
                'chapters.id' => 3,
                'isbns.id' => 1,
                'books.title' => 'LOTR 1',
                'authors.name' => 'Tolkien',
                'chapters.title' => 'LOTR 1.3',
                'isbns.value' => 'lotr-1',
            ],
            3 => [
                'books.id' => 2,
                'authors.id' => 2,
                'chapters.id' => 4,
                'isbns.id' => 2,
                'books.title' => 'LOTR 2',
                'authors.name' => 'Tolkien',
                'chapters.title' => 'LOTR 2.1',
                'isbns.value' => 'lotr-2',
            ],
            4 => [
                'books.id' => 2,
                'authors.id' => 2,
                'chapters.id' => 5,
                'isbns.id' => 2,
                'books.title' => 'LOTR 2',
                'authors.name' => 'Tolkien',
                'chapters.title' => 'LOTR 2.2',
                'isbns.value' => 'lotr-2',
            ],
            5 => [
                'books.id' => 2,
                'authors.id' => 2,
                'chapters.id' => 6,
                'isbns.id' => 2,
                'books.title' => 'LOTR 2',
                'authors.name' => 'Tolkien',
                'chapters.title' => 'LOTR 2.3',
                'isbns.value' => 'lotr-2',
            ],
            6 => [
                'books.id' => 3,
                'authors.id' => 2,
                'chapters.id' => 7,
                'isbns.id' => 3,
                'books.title' => 'LOTR 3',
                'authors.name' => 'Tolkien',
                'chapters.title' => 'LOTR 3.1',
                'isbns.value' => 'lotr-3',
            ],
            7 => [
                'books.id' => 3,
                'authors.id' => 2,
                'chapters.id' => 8,
                'isbns.id' => 3,
                'books.title' => 'LOTR 3',
                'authors.name' => 'Tolkien',
                'chapters.title' => 'LOTR 3.2',
                'isbns.value' => 'lotr-3',
            ],
            8 => [
                'books.id' => 3,
                'authors.id' => 2,
                'chapters.id' => 9,
                'isbns.id' => 3,
                'books.title' => 'LOTR 3',
                'authors.name' => 'Tolkien',
                'chapters.title' => 'LOTR 3.3',
                'isbns.value' => 'lotr-3',
            ],
            9 => [
                'books.id' => 4,
                'authors.id' => 1,
                'chapters.id' => 10,
                'isbns.id' => 4,
                'books.title' => 'Timeline Taxi',
                'authors.name' => 'Brent',
                'chapters.title' => 'Timeline Taxi Chapter 1',
                'isbns.value' => 'tt',
            ],
            10 => [
                'books.id' => 4,
                'authors.id' => 1,
                'chapters.id' => 11,
                'isbns.id' => 4,
                'books.title' => 'Timeline Taxi',
                'authors.name' => 'Brent',
                'chapters.title' => 'Timeline Taxi Chapter 2',
                'isbns.value' => 'tt',
            ],
            11 => [
                'books.id' => 4,
                'authors.id' => 1,
                'chapters.id' => 12,
                'isbns.id' => 4,
                'books.title' => 'Timeline Taxi',
                'authors.name' => 'Brent',
                'chapters.title' => 'Timeline Taxi Chapter 3',
                'isbns.value' => 'tt',
            ],
            12 => [
                'books.id' => 4,
                'authors.id' => 1,
                'chapters.id' => 13,
                'isbns.id' => 4,
                'books.title' => 'Timeline Taxi',
                'authors.name' => 'Brent',
                'chapters.title' => 'Timeline Taxi Chapter 4',
                'isbns.value' => 'tt',
            ],
        ];
    }
}
