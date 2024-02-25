<?php

declare(strict_types=1);

use App\Migrations\CreateAuthorTable;
use App\Migrations\CreateBookTable;
use App\Modules\Books\BookController;
use App\Modules\Books\Models\Author;
use App\Modules\Books\Models\Book;
use Tempest\Database\Id;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Http\Method;
use Tempest\Http\Request;
use Tempest\Http\Router;
use Tempest\Http\Status;
use Tests\Tempest\TestCase;
use function Tempest\request;
use function Tempest\uri;

uses(TestCase::class);

test('from container', function () {
    $this->server(
        method: Method::POST,
        uri: '/test',
        body: ['test'],
    );

    $request = $this->container->get(Request::class);

    expect($request->method)->toEqual(Method::POST);
    expect($request->uri)->toEqual('/test');
    expect($request->body)->toEqual(['test']);
});

test('custom request test', function () {
    $router = $this->container->get(Router::class);

    $body = [
        'title' => 'test-title',
        'text' => 'test-text',
    ];

    $this->server(
        method: Method::POST,
        uri: '/test',
        body: $body,
    );

    $response = $router->dispatch(request('/create-post')->post($body));

    expect($response->getStatus())->toEqual(Status::OK);
    expect($response->getBody())->toEqual('test-title test-text');
});

test('custom request test with validation', function () {
    $this->migrate(CreateMigrationsTable::class, CreateBookTable::class);

    $router = $this->container->get(Router::class);

    $body = [
        'title' => 'a',
    ];

    $uri = uri([BookController::class, 'store']);

    $this->server(
        method: Method::POST,
        uri: $uri,
        body: $body,
    );

    $response = $router->dispatch(request($uri)->post($body));

    expect($response->getStatus())->toBe(Status::FOUND);
    $book = Book::find(new Id(1));
    expect($book->id->id)->toBe(1);
    expect($book->title)->toBe('a');
});

test('custom request test with nested validation', function () {
    $this->migrate(
        CreateMigrationsTable::class,
        CreateBookTable::class,
        CreateAuthorTable::class
    );

    $router = $this->container->get(Router::class);

    $body = [
        'title' => 'a',
        'author.name' => 'b',
    ];

    $uri = uri([BookController::class, 'storeWithAuthor']);

    $this->server(
        method: Method::POST,
        uri: $uri,
        body: $body,
    );

    $response = $router->dispatch(request($uri)->post($body));

    expect($response->getStatus())->toBe(Status::FOUND);
    $book = Book::find(new Id(1), relations: [Author::class]);
    expect($book->id->id)->toBe(1);
    expect($book->title)->toBe('a');
    expect($book->author->name)->toBe('b');
});
