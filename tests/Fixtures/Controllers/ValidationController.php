<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Controllers;

use Tempest\Http\Response;
use Tempest\Http\Responses\Json;
use Tempest\Http\Responses\Ok;
use Tempest\Http\Responses\Redirect;
use Tempest\Router\Get;
use Tempest\Router\Post;
use Tests\Tempest\Fixtures\Modules\Books\Models\Book;
use Tests\Tempest\Fixtures\Requests\BookRequest;
use Tests\Tempest\Fixtures\Requests\SensitiveFieldRequest;
use Tests\Tempest\Fixtures\Requests\ValidationRequest;

use function Tempest\Router\uri;

final readonly class ValidationController
{
    #[Get('/test-validation-responses')]
    public function get(): Response
    {
        return new Ok();
    }

    #[Post('/test-validation-responses')]
    public function store(ValidationRequest $request): Response
    {
        return new Redirect(uri([self::class, 'get']));
    }

    #[Post('/test-sensitive-validation')]
    public function storeSensitive(SensitiveFieldRequest $request): Response
    {
        return new Redirect(uri([self::class, 'get']));
    }

    #[Get(uri: '/test-validation-responses-json/{book}')]
    public function book(Book $book): Response
    {
        $book->load('author');

        return new Json([
            'id' => $book->id->value,
            'title' => $book->title,
            'author' => [
                'id' => $book->author->id->value,
                'name' => $book->author->name,
            ],
        ]);
    }

    #[Post(uri: '/test-validation-responses-json/{book}')]
    public function updateBook(BookRequest $request, Book $book): Response
    {
        $book->load('author');

        $book->update(title: $request->get('title'));

        return new Json([
            'id' => $book->id->value,
            'title' => $book->title,
            'author' => [
                'id' => $book->author->id->value,
                'name' => $book->author->name,
            ],
            'chapters' => $book->chapters,
            'isbn' => $book->isbn,
        ]);
    }
}
