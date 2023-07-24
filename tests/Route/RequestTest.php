<?php

declare(strict_types=1);

namespace Tests\Tempest\Route;

use App\Migrations\CreateAuthorTable;
use App\Migrations\CreateBookTable;
use App\Modules\Books\BookController;
use App\Modules\Books\Models\Author;
use App\Modules\Books\Models\Book;
use Tempest\Database\Id;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Http\Method;
use Tempest\Http\Status;
use Tempest\Interfaces\Request;
use Tempest\Interfaces\Router;

use function Tempest\request;
use function Tempest\uri;

use Tests\Tempest\TestCase;

class RequestTest extends TestCase
{
    /** @test */
    public function from_container()
    {
        $this->server(
            method: Method::POST,
            uri: '/test',
            body: ['test'],
        );

        $request = $this->container->get(Request::class);

        $this->assertEquals(Method::POST, $request->method);
        $this->assertEquals('/test', $request->uri);
        $this->assertEquals(['test'], $request->body);
    }

    /** @test */
    public function custom_request_test()
    {
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

        $this->assertEquals(Status::HTTP_200, $response->getStatus());
        $this->assertEquals('test-title test-text', $response->getBody());
    }

    /** @test */
    public function custom_request_test_with_validation()
    {
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

        $this->assertSame(Status::HTTP_302, $response->getStatus());
        $book = Book::find(new Id(1));
        $this->assertSame(1, $book->id->id);
        $this->assertSame('a', $book->title);
    }

    /** @test */
    public function custom_request_test_with_nested_validation()
    {
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

        $this->assertSame(Status::HTTP_302, $response->getStatus());
        $book = Book::find(new Id(1), relations: [Author::class]);
        $this->assertSame(1, $book->id->id);
        $this->assertSame('a', $book->title);
        $this->assertSame('b', $book->author->name);
    }
}
