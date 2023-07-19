<?php

declare(strict_types=1);

namespace App\Modules\Books;

use App\Modules\Books\Models\Book;
use App\Modules\Books\Requests\CreateBookRequest;
use Tempest\Http\Get;
use Tempest\Http\Post;
use Tempest\Interfaces\Request;
use Tempest\Interfaces\Response;

final readonly class BookController
{
    #[Get('/books/{book}')]
    public function show(Book $book): Response
    {
        return response()->ok()->body($book->title);
    }

    #[Post('/books')]
    public function store(CreateBookRequest $request): Response
    {
        $book = map($request)->to(Book::class)->save();

        return redirect(BookController::class, 'show', book: $book->id);
    }

    #[Post('/books/{book}')]
    public function update(Book $book, Request $request): Response
    {
        // TODO request mapper
        $book = map($request)->to($book)->save();

        return redirect(BookController::class, 'show', book: $book->id);
    }
}
