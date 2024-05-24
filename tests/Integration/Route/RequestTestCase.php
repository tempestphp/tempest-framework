<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Route;

use App\Migrations\CreateAuthorTable;
use App\Migrations\CreateBookTable;
use App\Modules\Books\BookController;
use App\Modules\Books\Models\Author;
use App\Modules\Books\Models\Book;
use Tempest\Database\Id;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Http\Method;
use Tempest\Http\RequestFactory;
use Tempest\Http\Status;
use function Tempest\uri;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 * @small
 */
class RequestTestCase extends FrameworkIntegrationTestCase
{
    public function test_from_factory()
    {
        $_SERVER['REQUEST_METHOD'] = Method::POST->value;
        $_SERVER['REQUEST_URI'] = '/test';
        $_POST = ['test'];
        $_SERVER['HTTP_X-TEST'] = 'test';
        $_COOKIE['test'] = 'test';

        $request = (new RequestFactory())->make();

        $this->assertEquals(Method::POST->value, $request->getMethod());
        $this->assertEquals('/test', $request->getUri()->getPath());
        $this->assertEquals(['test'], $request->getParsedBody());
        $this->assertEquals(['x-test' => ['test']], $request->getHeaders());
        $this->assertEquals(['test' => 'test'], $request->getCookieParams());
    }

    public function test_custom_request_test()
    {
        $response = $this->http
            ->post(
                uri: '/create-post',
                body: [
                    'title' => 'test-title',
                    'text' => 'test-text',
                ],
            )
            ->assertOk();

        $this->assertEquals('test-title test-text', $response->getBody());
    }

    public function test_generic_request_can_map_to_custom_request()
    {
        $response = $this->http
            ->post(
                uri: '/create-post',
                body: [
                    'title' => 'test-title',
                    'text' => 'test-text',
                ],
            )
            ->assertOk();

        $this->assertEquals('test-title test-text', $response->getBody());
    }

    public function test_custom_request_test_with_validation()
    {
        $this->migrate(CreateMigrationsTable::class, CreateBookTable::class);

        $this->http
            ->post(
                uri: uri([BookController::class, 'store']),
                body: [
                    'title' => 'a',
                ],
            )
            ->assertStatus(Status::FOUND);

        $book = Book::find(new Id(1));
        $this->assertSame(1, $book->id->id);
        $this->assertSame('a', $book->title);
    }

    public function test_custom_request_test_with_nested_validation()
    {
        $this->migrate(
            CreateMigrationsTable::class,
            CreateBookTable::class,
            CreateAuthorTable::class,
        );

        $this->http
            ->post(
                uri: uri([BookController::class, 'storeWithAuthor']),
                body: [
                    'title' => 'a',
                    'author.name' => 'b',
                ],
            )
            ->assertStatus(Status::FOUND);

        $book = Book::find(new Id(1), relations: [Author::class]);
        $this->assertSame(1, $book->id->id);
        $this->assertSame('a', $book->title);
        $this->assertSame('b', $book->author->name);
    }
}
