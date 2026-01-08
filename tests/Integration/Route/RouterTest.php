<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Route;

use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Stream;
use Laminas\Diactoros\Uri;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Http\ContentType;
use Tempest\Http\Responses\Ok;
use Tempest\Http\Status;
use Tempest\Router\GenericRouter;
use Tempest\Router\RouteConfig;
use Tempest\Router\Router;
use Tempest\Router\SecFetchMode;
use Tempest\Router\SecFetchSite;
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
        $this->database->migrate(
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
                'Content-Type' => ContentType::JSON->value,
                'Sec-Fetch-Site' => SecFetchSite::SAME_ORIGIN->value,
                'Sec-Fetch-Mode' => SecFetchMode::CORS->value,
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

    public function test_router_returns_json_exception_when_accepts_json(): void
    {
        $this->registerRoute([Http500Controller::class, 'throwsException']);

        $this->http
            ->get('/throws-exception', headers: ['Accept' => 'application/json'])
            ->assertStatus(Status::INTERNAL_SERVER_ERROR)
            ->assertJsonHasKeys('message');
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

    public function test_stateless_decorator(): void
    {
        $this->http
            ->get('/stateless')
            ->assertOk()
            ->assertDoesNotHaveCookie('tempest_session_id');
    }

    public function test_prefix_decorator(): void
    {
        $this->http
            ->get('/prefix/method/endpoint')
            ->assertOk();
    }

    public function test_with_middleware_decorator(): void
    {
        $this->http
            ->get('/with-decorated-middleware')
            ->assertOk()
            ->assertHasHeader('middleware');
    }

    public function test_without_middleware_decorator(): void
    {
        $this->http
            ->get('/without-decorated-middleware')
            ->assertOk()
            ->assertDoesNotHaveHeader('set-cookie');
    }

    public function test_optional_parameter_with_required_parameter(): void
    {
        $this->http
            ->get('/articles/123')
            ->assertOk()
            ->assertSee('Article 123 without slug');

        $this->http
            ->get('/articles/123/my-article')
            ->assertOk()
            ->assertSee('Article 123 with slug my-article');
    }

    public function test_optional_parameter_only(): void
    {
        $this->http
            ->get('/users')
            ->assertOk()
            ->assertSee('All users');

        $this->http
            ->get('/users/456')
            ->assertOk()
            ->assertSee('User 456');
    }

    public function test_multiple_optional_parameters(): void
    {
        $this->http
            ->get('/posts')
            ->assertOk()
            ->assertSee('All posts');

        $this->http
            ->get('/posts/789')
            ->assertOk()
            ->assertSee('Post 789');

        $this->http
            ->get('/posts/789/tech')
            ->assertOk()
            ->assertSee('Post 789 in category tech');
    }
}
