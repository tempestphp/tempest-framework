<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Modules\Books;

use Tempest\Http\Get;
use Tempest\Http\Post;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\Ok;
use Tempest\Http\Responses\Redirect;
use function Tempest\map;
use function Tempest\uri;
use function Tempest\view;
use Tempest\View\View;
use Tests\Tempest\Fixtures\Modules\Books\Models\Book;
use Tests\Tempest\Fixtures\Modules\Books\Requests\CreateBookRequest;

final readonly class BookController
{
    #[Get('/book/create')]
    public function index(): View
    {
        return view(__DIR__ . '/create.book.view.php');
    }

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
