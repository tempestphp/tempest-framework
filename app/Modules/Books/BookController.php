<?php

declare(strict_types=1);

namespace App\Modules\Books;

use App\Modules\Books\Models\Book;
use App\Modules\Books\Requests\CreateBookRequest;
use App\Modules\Books\Requests\StoreBookRequest;
use Tempest\Http\Get;
use Tempest\Http\Post;
use Tempest\Interfaces\Response;
use Tempest\Interfaces\View;

final readonly class BookController
{
    #[Get('/books/{book}')]
    public function show(Book $book): View
    {
        return new BookDetailView(book: $book);
    }

    #[Post('/books')]
    public function store(CreateBookRequest $request): Response
    {
        $book = map($request)->to(Book::class)->save();

        return redirect(BookController::class, 'show', book: $book);
    }

    #[Post('/books/{book}')]
    public function update(Book $book, StoreBookRequest $request): Response
    {
        $book = map($request)->to($book)->save();

        return redirect(BookController::class, 'show', book: $book);
    }
}
