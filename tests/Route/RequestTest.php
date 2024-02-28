<?php

declare(strict_types=1);

namespace Tests\Tempest\Route;

use App\Migrations\CreateAuthorTable;
use App\Migrations\CreateBookTable;
use App\Modules\Books\BookController;
use App\Modules\Books\Models\Author;
use App\Modules\Books\Models\Book;
use App\Modules\Books\Requests\CreateBookRequest;
use App\Modules\Posts\PostRequest;
use Tempest\Database\Id;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Http\GenericRequest;
use Tempest\Http\Method;
use Tempest\Http\Request;
use Tempest\Http\Status;
use function Tempest\uri;
use Tests\Tempest\TestCase;

class RequestTest extends TestCase
{
    /** @test */
    public function from_container()
    {
        $_SERVER['REQUEST_METHOD'] = Method::POST->value;
        $_SERVER['REQUEST_URI'] = '/test';
        $_POST = ['test'];
        $_SERVER['HTTP_X-TEST'] = 'test';

        $request = $this->container->get(Request::class);

        $this->assertEquals(Method::POST, $request->getMethod());
        $this->assertEquals('/test', $request->getUri());
        $this->assertEquals(['test'], $request->getBody());
        $this->assertEquals(['x-test' => 'test'], $request->getHeaders());
    }

    /** @test */
    public function custom_request_test()
    {
        $response = $this->send(new PostRequest(
            method: Method::POST,
            uri: '/create-post',
            body: [
                'title' => 'test-title',
                'text' => 'test-text',
            ],
        ));

        $this->assertEquals(Status::OK, $response->getStatus());
        $this->assertEquals('test-title test-text', $response->getBody());
    }

    /** @test */
    public function generic_request_can_map_to_custom_request()
    {
        $response = $this->send(new GenericRequest(
            method: Method::POST,
            uri: '/create-post',
            body: [
                'title' => 'test-title',
                'text' => 'test-text',
            ],
        ));

        $this->assertEquals(Status::OK, $response->getStatus());
        $this->assertEquals('test-title test-text', $response->getBody());
    }

    /** @test */
    public function custom_request_test_with_validation()
    {
        $this->migrate(CreateMigrationsTable::class, CreateBookTable::class);

        $response = $this->send(new CreateBookRequest(
            method: Method::POST,
            uri: uri([BookController::class, 'store']),
            body:  [
                'title' => 'a',
            ],
        ));

        $this->assertSame(Status::FOUND, $response->getStatus());
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

        $response = $this->send(new GenericRequest(
            method: Method::POST,
            uri: uri([BookController::class, 'storeWithAuthor']),
            body: [
                'title' => 'a',
                'author.name' => 'b',
            ],
        ));

        $this->assertSame(Status::FOUND, $response->getStatus());
        $book = Book::find(new Id(1), relations: [Author::class]);
        $this->assertSame(1, $book->id->id);
        $this->assertSame('a', $book->title);
        $this->assertSame('b', $book->author->name);
    }
}
