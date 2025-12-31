<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Route;

use PHPUnit\Framework\Attributes\TestWith;
use Tempest\Cryptography\Encryption\Encrypter;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\PrimaryKey;
use Tempest\Http\GenericRequest;
use Tempest\Http\Method;
use Tempest\Http\RequestFactory;
use Tempest\Http\Status;
use Tests\Tempest\Fixtures\Migrations\CreateAuthorTable;
use Tests\Tempest\Fixtures\Migrations\CreateBookTable;
use Tests\Tempest\Fixtures\Migrations\CreatePublishersTable;
use Tests\Tempest\Fixtures\Modules\Books\BookController;
use Tests\Tempest\Fixtures\Modules\Books\Models\Book;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Route\Fixtures\MemoryInputStream;

use function Tempest\Router\uri;

/**
 * @internal
 */
final class RequestTest extends FrameworkIntegrationTestCase
{
    public function test_request_get(): void
    {
        $request = new GenericRequest(
            method: Method::GET,
            uri: '/',
            body: [],
        );

        $this->assertSame('default', $request->get('a', 'default'));

        $request = new GenericRequest(
            method: Method::GET,
            uri: '/?a=1',
            body: [],
        );

        $this->assertSame('1', $request->get('a', 'default'));

        $request = new GenericRequest(
            method: Method::GET,
            uri: '/?a=1',
            body: [
                'a' => '2',
            ],
        );

        $this->assertSame('2', $request->get('a', 'default'));

        $request = new GenericRequest(
            method: Method::GET,
            uri: '/',
            body: [
                'a' => '2',
            ],
        );

        $this->assertSame('2', $request->get('a', 'default'));
    }

    public function test_from_factory(): void
    {
        $_COOKIE = [];

        $_SERVER['REQUEST_METHOD'] = Method::POST->value;
        $_SERVER['REQUEST_URI'] = '/test';
        $_SERVER['HTTP_X-TEST'] = 'test';
        $_COOKIE['test'] = $this->container
            ->get(Encrypter::class)
            ->encrypt('test')
            ->serialize();

        $request = new RequestFactory(new MemoryInputStream([
            'test' => 'test',
        ]))->make();

        $this->assertEquals(Method::POST->value, $request->getMethod());
        $this->assertEquals('/test', $request->getUri()->getPath());
        $this->assertEquals(['test' => 'test'], $request->getParsedBody());
        $this->assertEquals(['x-test' => ['test']], $request->getHeaders());
        $this->assertCount(1, $request->getCookieParams());
        $this->assertArrayHasKey('test', $request->getCookieParams());
        $this->assertSame('test', $this->container->get(Encrypter::class)->decrypt($request->getCookieParams()['test']));
    }

    public function test_custom_request_test(): void
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

        $this->assertEquals('test-title test-text', $response->body);
    }

    public function test_generic_request_can_map_to_custom_request(): void
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

        $this->assertEquals('test-title test-text', $response->body);
    }

    public function test_custom_request_test_with_validation(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        $this->http
            ->post(
                uri: uri([BookController::class, 'store']),
                body: [
                    'title' => 'a',
                    'chapters' => [],
                ],
                headers: [
                    'referer' => ['/'],
                ],
            )
            ->assertHasNoValidationsErrors()
            ->assertStatus(Status::FOUND);

        $book = Book::get(new PrimaryKey(1));
        $this->assertSame(1, $book->id->value);
        $this->assertSame('a', $book->title);
    }

    public function test_custom_request_test_with_nested_validation(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        $this->http
            ->post(
                uri: uri([BookController::class, 'storeWithAuthor']),
                body: [
                    'title' => 'a',
                    'author.name' => 'b',
                    'chapters' => [],
                ],
                headers: [
                    'referer' => ['/'],
                ],
            )
            ->assertHasNoValidationsErrors()
            ->assertStatus(Status::FOUND);

        $book = Book::get(new PrimaryKey(1), relations: ['author']);
        $this->assertSame(1, $book->id->value);
        $this->assertSame('a', $book->title);
        $this->assertSame('b', $book->author->name);
    }

    public function test_has(): void
    {
        $request = new GenericRequest(
            method: Method::GET,
            uri: '/?bar',
            body: [
                'foo' => false,
                'nested' => [
                    'baz' => 'quux',
                ],
            ],
        );

        $this->assertTrue($request->hasBody('foo'));
        $this->assertTrue($request->hasQuery('bar'));
        $this->assertTrue($request->has('bar'));
        $this->assertTrue($request->has('foo'));
        $this->assertFalse($request->hasQuery('foo'));
        $this->assertFalse($request->hasBody('bar'));
        $this->assertFalse($request->hasBody('unknown'));
        $this->assertFalse($request->hasQuery('unknown'));
        $this->assertFalse($request->has('unknown'));
        $this->assertTrue($request->has('nested.baz'));
        $this->assertFalse($request->has('nested.bar'));
        $this->assertTrue($request->hasBody('nested.baz'));
        $this->assertFalse($request->hasBody('nested.bar'));
    }

    #[TestWith([[], null, false])]
    #[TestWith([[], '', false])]
    #[TestWith([[], 'foo', true])]
    #[TestWith([['foo' => 'bar'], null, true])]
    #[TestWith([['foo' => 'bar'], 'foo', true])]
    public function test_body(array $body, ?string $raw, bool $expected): void
    {
        $request = new GenericRequest(
            method: Method::GET,
            uri: '/?bar',
            body: $body,
            raw: $raw,
        );

        $this->assertSame($expected, $request->hasBody());
    }
}
