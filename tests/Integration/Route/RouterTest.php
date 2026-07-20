<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Route;

use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Stream;
use Laminas\Diactoros\Uri;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Clock\Clock;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Http\ContentType;
use Tempest\Http\Responses\Ok;
use Tempest\Http\Session\Session;
use Tempest\Http\Session\SessionId;
use Tempest\Http\Session\SessionManager;
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
    #[Test]
    public function dispatch(): void
    {
        $router = $this->container->get(GenericRouter::class);

        $response = $router->dispatch($this->http->makePsrRequest('/test'));

        $this->assertEquals(Status::OK, $response->status);
        $this->assertEquals('test', $response->body);
    }

    #[Test]
    public function dispatch_with_parameter(): void
    {
        $router = $this->container->get(GenericRouter::class);

        $response = $router->dispatch($this->http->makePsrRequest('/test/1/a/extra'));

        $this->assertEquals(Status::OK, $response->status);
        $this->assertEquals('1a/extra', $response->body);
    }

    #[Test]
    public function dispatch_with_parameter_with_custom_regex(): void
    {
        $router = $this->container->get(GenericRouter::class);

        $response = $router->dispatch($this->http->makePsrRequest('/test/1/a'));

        $this->assertEquals(Status::OK, $response->status);
        $this->assertEquals('1a', $response->body);
    }

    #[Test]
    public function dispatch_with_parameter_with_complex_custom_regex(): void
    {
        $router = $this->container->get(GenericRouter::class);

        $response = $router->dispatch($this->http->makePsrRequest('/test/1'));

        $this->assertEquals(Status::OK, $response->status);
        $this->assertEquals('1', $response->body);
    }

    #[Test]
    public function with_view(): void
    {
        $router = $this->container->get(GenericRouter::class);

        $response = $router->dispatch($this->http->makePsrRequest('/view'));

        $this->assertInstanceOf(Ok::class, $response);
    }

    #[Test]
    public function route_binding(): void
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

    #[Test]
    public function route_binding_with_nonexistent_model_returns_404(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        $this->http
            ->get('/books/999')
            ->assertNotFound();
    }

    #[Test]
    public function middleware(): void
    {
        $this
            ->container->get(RouteConfig::class)
            ->middleware->add(TestGlobalMiddleware::class);
        $router = $this->container->get(GenericRouter::class);

        $response = $router->dispatch($this->http->makePsrRequest('/with-middleware'));

        $this->assertEquals(['from-dependency'], $response->getHeader('middleware')->values);
        $this->assertEquals(['yes'], $response->getHeader('global-middleware')->values);
    }

    #[Test]
    public function skip_middleware(): void
    {
        $this
            ->container->get(RouteConfig::class)
            ->middleware->add(TestMiddleware::class);

        $this->http
            ->get('/without-middleware')
            ->assertDoesNotHaveHeader('middleware');
    }

    #[Test]
    public function trailing_slash(): void
    {
        $this->http
            ->get('/test')
            ->assertOk();

        $this->http
            ->get('/test/1/a/')
            ->assertOk();
    }

    #[Test]
    public function repeated_routes(): void
    {
        $this->http->get('/repeated/a')->assertOk();
        $this->http->get('/repeated/b')->assertOk();
        $this->http->get('/repeated/c')->assertOk();
        $this->http->get('/repeated/d')->assertOk();
        $this->http->post('/repeated/e')->assertOk();
        $this->http->post('/repeated/f')->assertOk();
    }

    #[Test]
    public function enum_route_binding(): void
    {
        $this->http->get('/with-enum/foo')->assertOk();
        $this->http->get('/with-enum/bar')->assertOk();
        $this->http->get('/with-enum/unknown')->assertNotFound();
    }

    #[Test]
    public function json_request(): void
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

    #[Test]
    public function discovers_response_processors(): void
    {
        $this->http
            ->get('/', headers: ['X-Processed' => 'false'])
            ->assertHeaderContains('X-Processed', 'true')
            ->assertOk();
    }

    #[Test]
    public function can_add_response_processor(): void
    {
        $this->container->get(RouteConfig::class)->addResponseProcessor(TestProcessedResponseProcessor::class);

        $this->http
            ->get('/')
            ->assertHeaderContains('X-Processed', 'true')
            ->assertOk();
    }

    #[Test]
    public function error_response_processor_does_not_throw_http_exceptions_if_there_is_a_body(): void
    {
        $this->http->registerRoute([Http500Controller::class, 'serverErrorWithBody']);

        $this->http
            ->get('/returns-server-error-with-body')
            ->assertStatus(Status::INTERNAL_SERVER_ERROR)
            ->assertSee('custom error');
    }

    #[Test]
    public function converts_to_response(): void
    {
        $this->http->registerRoute([Http500Controller::class, 'convertsToResponse']);

        $this->http
            ->get('/returns-converts-to-response')
            ->assertStatus(Status::FOUND)
            ->assertHeaderContains('Location', 'https://tempestphp.com');
    }

    #[Test]
    public function router_returns_json_exception_when_accepts_json(): void
    {
        $this->http->registerRoute([Http500Controller::class, 'throwsException']);

        $this->http
            ->get('/throws-exception', headers: ['Accept' => 'application/json'])
            ->assertStatus(Status::INTERNAL_SERVER_ERROR)
            ->assertJsonHasKeys('message');
    }

    #[Test]
    public function head_requests(): void
    {
        $this->http->registerRoute([HeadController::class, 'implicitHead']);
        $this->http->registerRoute([HeadController::class, 'explicitHead']);

        $this->http
            ->head('/implicit-head')
            ->assertOk()
            ->assertHasHeader('x-custom');

        $this->http
            ->head('/explicit-head')
            ->assertOk()
            ->assertHasHeader('x-custom');
    }

    #[Test]
    public function stateless_decorator(): void
    {
        $this->http
            ->get('/stateless')
            ->assertOk()
            ->assertDoesNotHaveCookie('tempest_session_id');
    }

    #[Test]
    public function stateless_decorator_does_not_manage_session(): void
    {
        $sessionManager = new RouteTestingSessionManager($this->container->get(Clock::class));

        $this->container->singleton(SessionManager::class, fn () => $sessionManager);

        $this->http
            ->get('/stateless')
            ->assertOk();

        $this->assertSame(0, $sessionManager->createdSessions);
        $this->assertSame(0, $sessionManager->savedSessions);
    }

    #[Test]
    public function prefix_decorator(): void
    {
        $this->http
            ->get('/prefix/method/endpoint')
            ->assertOk();
    }

    #[Test]
    public function with_middleware_decorator(): void
    {
        $this->http
            ->get('/with-decorated-middleware')
            ->assertOk()
            ->assertHasHeader('middleware');
    }

    #[Test]
    public function without_middleware_decorator(): void
    {
        $this->http
            ->get('/without-decorated-middleware')
            ->assertOk()
            ->assertDoesNotHaveHeader('set-cookie');
    }

    #[Test]
    public function optional_parameter_with_required_parameter(): void
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

    #[Test]
    public function optional_parameter_only(): void
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

    #[Test]
    public function multiple_optional_parameters(): void
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

final class RouteTestingSessionManager implements SessionManager
{
    public int $createdSessions = 0;

    public int $savedSessions = 0;

    public function __construct(
        private readonly Clock $clock,
    ) {}

    public function getOrCreate(SessionId $id): Session
    {
        $this->createdSessions++;

        $now = $this->clock->now();

        return new Session(
            id: $id,
            createdAt: $now,
            lastActiveAt: $now,
        );
    }

    public function save(Session $session): void
    {
        $this->savedSessions++;
    }

    public function delete(Session $session): void {}

    public function isValid(Session $session): bool
    {
        return true;
    }

    public function deleteExpiredSessions(): void {}
}
