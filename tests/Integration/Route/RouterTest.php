<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Route;

use Tempest\Core\AppConfig;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Http\GenericRouter;
use Tempest\Http\Responses\Ok;
use Tempest\Http\Router;
use Tempest\Http\Status;
use function Tempest\uri;
use Tests\Tempest\Fixtures\Controllers\ControllerWithEnumBinding;
use Tests\Tempest\Fixtures\Controllers\EnumForController;
use Tests\Tempest\Fixtures\Controllers\TestController;
use Tests\Tempest\Fixtures\Controllers\TestGlobalMiddleware;
use Tests\Tempest\Fixtures\Migrations\CreateAuthorTable;
use Tests\Tempest\Fixtures\Migrations\CreateBookTable;
use Tests\Tempest\Fixtures\Modules\Books\Models\Author;
use Tests\Tempest\Fixtures\Modules\Books\Models\Book;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class RouterTest extends FrameworkIntegrationTestCase
{
    public function test_dispatch(): void
    {
        $router = $this->container->get(GenericRouter::class);

        $response = $router->dispatch($this->http->makePsrRequest('/test'));

        $this->assertEquals(Status::OK, $response->getStatus());
        $this->assertEquals('test', $response->getBody());
    }

    public function test_dispatch_with_parameter(): void
    {
        $router = $this->container->get(GenericRouter::class);

        $response = $router->dispatch($this->http->makePsrRequest('/test/1/a/extra'));

        $this->assertEquals(Status::OK, $response->getStatus());
        $this->assertEquals('1a/extra', $response->getBody());
    }

    public function test_dispatch_with_parameter_with_custom_regex(): void
    {
        $router = $this->container->get(GenericRouter::class);

        $response = $router->dispatch($this->http->makePsrRequest('/test/1/a'));

        $this->assertEquals(Status::OK, $response->getStatus());
        $this->assertEquals('1a', $response->getBody());
    }

    public function test_dispatch_with_parameter_with_complex_custom_regex(): void
    {
        $router = $this->container->get(GenericRouter::class);

        $response = $router->dispatch($this->http->makePsrRequest('/test/1'));

        $this->assertEquals(Status::OK, $response->getStatus());
        $this->assertEquals('1', $response->getBody());
    }

    public function test_generate_uri(): void
    {
        $router = $this->container->get(GenericRouter::class);

        $this->assertEquals('/test/1/a', $router->toUri([TestController::class, 'withParams'], id: 1, name: 'a'));

        $this->assertEquals('/test/1', $router->toUri([TestController::class, 'withComplexCustomRegexParams'], id: 1));

        $this->assertEquals('/test/1/a/b', $router->toUri([TestController::class, 'withCustomRegexParams'], id: 1, name: 'a/b'));
        $this->assertEquals('/test', $router->toUri(TestController::class));

        $this->assertEquals('/test/1/a?q=hi&i=test', $router->toUri([TestController::class, 'withParams'], id: 1, name: 'a', q: 'hi', i: 'test'));

        $appConfig = new AppConfig(baseUri: 'https://test.com');
        $this->container->config($appConfig);
        $router = $this->container->get(GenericRouter::class);
        $this->assertEquals('https://test.com/test/1/a', $router->toUri([TestController::class, 'withParams'], id: 1, name: 'a'));

        $this->assertSame('https://test.com/abc', $router->toUri('/abc'));
        $this->assertEquals('https://test.com/test/1/a/b/c/d', $router->toUri([TestController::class, 'withCustomRegexParams'], id: 1, name: 'a/b/c/d'));
    }

    public function test_uri_generation_with_query_param(): void
    {
        $router = $this->container->get(GenericRouter::class);

        $uri = $router->toUri(TestController::class, test: 'foo');

        $this->assertSame('/test?test=foo', $uri);
    }

    public function test_with_view(): void
    {
        $router = $this->container->get(GenericRouter::class);

        $response = $router->dispatch($this->http->makePsrRequest('/view'));

        $this->assertInstanceOf(Ok::class, $response);
    }

    public function test_route_binding(): void
    {
        $this->migrate(
            CreateMigrationsTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        Book::create(
            title: 'Test',
            author: new Author(name: 'Brent'),
        );

        $router = $this->container->get(Router::class);

        $response = $router->dispatch($this->http->makePsrRequest('/books/1'));

        $this->assertSame(Status::OK, $response->getStatus());
        $this->assertSame('Test', $response->getBody());
    }

    public function test_middleware(): void
    {
        $router = $this->container->get(GenericRouter::class);

        $router->addMiddleware(TestGlobalMiddleware::class);

        $response = $router->dispatch($this->http->makePsrRequest('/with-middleware'));

        $this->assertEquals(['from-dependency'], $response->getHeader('middleware')->values);
        $this->assertEquals(['yes'], $response->getHeader('global-middleware')->values);
    }

    public function test_trailing_slash(): void
    {
        $this->http
            ->get('/test')
            ->assertOk();

        $this->http
            ->get('/test/1/a/')
            ->assertOk();
    }

    public function test_repeated_routes(): void
    {
        $this->http->get('/repeated/a')->assertOk();
        $this->http->get('/repeated/b')->assertOk();
        $this->http->get('/repeated/c')->assertOk();
        $this->http->get('/repeated/d')->assertOk();
        $this->http->post('/repeated/e')->assertOk();
        $this->http->post('/repeated/f')->assertOk();
    }

    public function test_enum_route_binding(): void
    {
        $this->http->get('/with-enum/foo')->assertOk();
        $this->http->get('/with-enum/bar')->assertOk();
        $this->http->get('/with-enum/unknown')->assertNotFound();
    }

    public function test_generate_uri_with_enum(): void
    {
        $this->assertSame(
            '/with-enum/foo',
            uri(ControllerWithEnumBinding::class, input: EnumForController::FOO),
        );

        $this->assertSame(
            '/with-enum/bar',
            uri(ControllerWithEnumBinding::class, input: EnumForController::BAR),
        );
    }
}
