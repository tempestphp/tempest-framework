<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Modules\Books;

use Tempest\Router\Get;
use Tempest\Router\Post;
use Tempest\Router\Request;
use Tempest\Router\Response;
use Tempest\Router\Responses\Ok;
use Tempest\Router\Responses\Redirect;
use Tests\Tempest\Fixtures\Modules\Books\Models\Book;
use Tests\Tempest\Fixtures\Modules\Books\Requests\CreateBookRequest;
use function Tempest\map;
use function Tempest\uri;

final readonly class BookController
{
    #[Get('/books/{book}')]
    public function show(Book $book): Response
    {
        return (new Ok($book->title));
    }

    #[Post('/books')]
    public function store(CreateBookRequest $request): Response
    {
        $book = map($request)->to(Book::class)->save();

        return new Redirect(uri([BookController::class, 'show'], book: $book->id));
    }

    #[Post('/books-with-author')]
    public function storeWithAuthor(Request $request): Response
    {
        $book = map($request)->to(Book::class)->save();

        return new Redirect(uri([BookController::class, 'show'], book: $book->id));
    }

    #[Post('/books/{book}')]
    public function update(Book $book, Request $request): Response
    {
        $book = map($request)->to($book)->save();

        return new Redirect(uri([BookController::class, 'show'], book: $book->id));
    }
}
