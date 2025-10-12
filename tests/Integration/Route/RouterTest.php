<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Route;

use Exception;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Stream;
use Laminas\Diactoros\Uri;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Http\HttpRequestFailed;
use Tempest\Http\Responses\Ok;
use Tempest\Http\Status;
use Tempest\Router\GenericRouter;
use Tempest\Router\RouteConfig;
use Tempest\Router\Router;
use Tests\Tempest\Fixtures\Controllers\TestGlobalMiddleware;
use Tests\Tempest\Fixtures\Controllers\TestMiddleware;
use Tests\Tempest\Fixtures\Migrations\CreateAuthorTable;
use Tests\Tempest\Fixtures\Migrations\CreateBookTable;
use Tests\Tempest\Fixtures\Migrations\CreatePublishersTable;
use Tests\Tempest\Fixtures\Modules\Books\Models\Author;
use Tests\Tempest\Fixtures\Modules\Books\Models\Book;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Route\Fixtures\HeadController;
use Tests\Tempest\Integration\Route\Fixtures\Http500Controller;

/**
 * @internal
 */
final class RouterTest extends FrameworkIntegrationTestCase
{
    public function test_dispatch(): void
    {
        $router = $this->container->get(GenericRouter::class);

        $response = $router->dispatch($this->http->makePsrRequest('/test'));

        $this->assertEquals(Status::OK, $response->status);
        $this->assertEquals('test', $response->body);
    }

    public function test_dispatch_with_parameter(): void
    {
        $router = $this->container->get(GenericRouter::class);

        $response = $router->dispatch($this->http->makePsrRequest('/test/1/a/extra'));

        $this->assertEquals(Status::OK, $response->status);
        $this->assertEquals('1a/extra', $response->body);
    }

    public function test_dispatch_with_parameter_with_custom_regex(): void
    {
        $router = $this->container->get(GenericRouter::class);

        $response = $router->dispatch($this->http->makePsrRequest('/test/1/a'));

        $this->assertEquals(Status::OK, $response->status);
        $this->assertEquals('1a', $response->body);
    }

    public function test_dispatch_with_parameter_with_complex_custom_regex(): void
    {
        $router = $this->container->get(GenericRouter::class);

        $response = $router->dispatch($this->http->makePsrRequest('/test/1'));

        $this->assertEquals(Status::OK, $response->status);
        $this->assertEquals('1', $response->body);
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
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        Book::create(
            title: 'Test',
            author: new Author(name: 'Brent'),
        );

        $router = $this->container->get(Router::class);

        $response = $router->dispatch($this->http->makePsrRequest('/books/1'));

        $this->assertSame(Status::OK, $response->status);
        $this->assertSame('Test', $response->body);
    }

    public function test_middleware(): void
    {
        $this
            ->container->get(RouteConfig::class)
            ->middleware->add(TestGlobalMiddleware::class);
        $router = $this->container->get(GenericRouter::class);

        $response = $router->dispatch($this->http->makePsrRequest('/with-middleware'));

        $this->assertEquals(['from-dependency'], $response->getHeader('middleware')->values);
        $this->assertEquals(['yes'], $response->getHeader('global-middleware')->values);
    }

    public function test_skip_middleware(): void
    {
        $this
            ->container->get(RouteConfig::class)
            ->middleware->add(TestMiddleware::class);

        $this->http
            ->get('/without-middleware')
            ->assertDoesNotHaveHeader('middleware');
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

    public function test_json_request(): void
    {
        $router = $this->container->get(Router::class);

        $response = $router->dispatch(new ServerRequest(
            uri: new Uri('/json-endpoint'),
            method: 'POST',
            body: new Stream(fopen(__DIR__ . '/request.json', 'r')),
            headers: [
                'Content-Type' => 'application/json',
            ],
        ));

        $this->assertSame(Status::OK, $response->status);
        $this->assertSame('foo', $response->body);
    }

    public function test_discovers_response_processors(): void
    {
        $this->http
            ->get('/', headers: ['X-Processed' => 'false'])
            ->assertHeaderContains('X-Processed', 'true')
            ->assertOk();
    }

    public function test_can_add_response_processor(): void
    {
        $this->container->get(RouteConfig::class)->addResponseProcessor(TestProcessedResponseProcessor::class);

        $this->http
            ->get('/')
            ->assertHeaderContains('X-Processed', 'true')
            ->assertOk();
    }

    public function test_error_response_processor_throws_http_exceptions_when_instructed(): void
    {
        $this->expectException(HttpRequestFailed::class);
        $this->expectExceptionCode(404);

        $this->http
            ->throwExceptions()
            ->get('/non-existent');
    }

    public function test_error_response_processor_throws_http_exceptions_if_there_is_a_body(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('oops');

        $this->registerRoute([Http500Controller::class, 'throwsException']);

        $this->http
            ->throwExceptions()
            ->get('/throws-exception');
    }

    public function test_throws_http_exception_when_returning_server_error(): void
    {
        $this->expectException(HttpRequestFailed::class);
        $this->expectExceptionCode(500);

        $this->registerRoute([Http500Controller::class, 'serverError']);

        $this->http
            ->throwExceptions()
            ->get('/returns-server-error');
    }

    public function test_error_response_processor_does_not_throw_http_exceptions_if_there_is_a_body(): void
    {
        $this->registerRoute([Http500Controller::class, 'serverErrorWithBody']);

        $this->http
            ->get('/returns-server-error-with-body')
            ->assertStatus(Status::INTERNAL_SERVER_ERROR)
            ->assertSee('custom error');
    }

    public function test_converts_to_response(): void
    {
        $this->registerRoute([Http500Controller::class, 'convertsToResponse']);

        $this->http
            ->get('/returns-converts-to-response')
            ->assertStatus(Status::FOUND)
            ->assertHeaderContains('Location', 'https://tempestphp.com');
    }

    public function test_head_requests(): void
    {
        $this->registerRoute([HeadController::class, 'implicitHead']);
        $this->registerRoute([HeadController::class, 'explicitHead']);

        $this->http
            ->head('/implicit-head')
            ->assertOk()
            ->assertHasHeader('x-custom');

        $this->http
            ->head('/explicit-head')
            ->assertOk()
            ->assertHasHeader('x-custom');
    }
}
