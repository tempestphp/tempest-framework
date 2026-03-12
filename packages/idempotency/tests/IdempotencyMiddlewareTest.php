<?php

declare(strict_types=1);

namespace Tempest\Idempotency\Tests;

use Generator;
use JsonSerializable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Tempest\Cache\GenericCache;
use Tempest\Discovery\SkipDiscovery;
use Tempest\Http\GenericRequest;
use Tempest\Http\GenericResponse;
use Tempest\Http\Method;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Status;
use Tempest\Idempotency\Attributes\Idempotent;
use Tempest\Idempotency\Attributes\IdempotentRoute;
use Tempest\Idempotency\Config\IdempotencyConfig;
use Tempest\Idempotency\Exceptions\IdempotencyMethodWasNotSupported;
use Tempest\Idempotency\Fingerprint\RequestFingerprintGenerator;
use Tempest\Idempotency\Middleware\IdempotencyMiddleware;
use Tempest\Idempotency\Store\CacheIdempotencyStore;
use Tempest\Idempotency\Store\IdempotencyRecord;
use Tempest\Idempotency\Store\IdempotencyState;
use Tempest\Idempotency\Store\IdempotencyStore;
use Tempest\Idempotency\Store\StoredResponse;
use Tempest\Idempotency\Support\IdempotencyKeyResolver;
use Tempest\Idempotency\Support\ProcessingOwner;
use Tempest\Idempotency\Tests\Fixtures\FixedScopeResolver;
use Tempest\Idempotency\Tests\Fixtures\RecordingCache;
use Tempest\Idempotency\Tests\Fixtures\RecordingStore;
use Tempest\Reflection\ClassReflector;
use Tempest\Reflection\MethodReflector;
use Tempest\Router\HttpMiddlewareCallable;
use Tempest\Router\MatchedRoute;
use Tempest\Router\Post;
use Tempest\Router\Route;
use Tempest\Router\RouteDecorator;
use Tempest\Router\Routing\Construction\DiscoveredRoute;

final class IdempotencyMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('Idempotency tests are not supported on Windows.');
        }
    }

    #[Test]
    public function idempotency_middleware_is_not_globally_discovered(): void
    {
        $reflector = new ClassReflector(IdempotencyMiddleware::class);

        $this->assertTrue($reflector->hasAttribute(SkipDiscovery::class));
    }

    #[Test]
    public function requires_an_idempotency_key_by_default(): void
    {
        $middleware = $this->createMiddleware('create');
        $calls = 0;

        $response = $middleware(
            new GenericRequest(Method::POST, '/orders', body: ['amount' => 100]),
            new HttpMiddlewareCallable(function (Request $_) use (&$calls): Response {
                $calls++;

                return new GenericResponse(Status::CREATED, ['ok' => true]);
            }),
        );

        $this->assertSame(Status::BAD_REQUEST, $response->status);
        $this->assertSame(0, $calls);
    }

    #[Test]
    public function replays_the_original_response_for_the_same_key_and_payload(): void
    {
        $middleware = $this->createMiddleware('create');
        $calls = 0;

        $request = new GenericRequest(
            Method::POST,
            '/orders',
            body: ['amount' => 100],
            headers: ['Idempotency-Key' => 'order-100'],
        );

        $next = new HttpMiddlewareCallable(function (Request $_) use (&$calls): Response {
            $calls++;

            return new GenericResponse(Status::CREATED, ['id' => 'order-1']);
        });

        $firstResponse = $middleware($request, $next);
        $secondResponse = $middleware($request, $next);

        $this->assertSame(Status::CREATED, $firstResponse->status);
        $this->assertSame(Status::CREATED, $secondResponse->status);
        $this->assertSame(1, $calls);
        $this->assertSame('true', $secondResponse->getHeader('idempotency-replayed')?->first());
        $this->assertSame(['id' => 'order-1'], $secondResponse->body);
    }

    #[Test]
    public function rejects_the_same_key_when_the_payload_changes(): void
    {
        $middleware = $this->createMiddleware('create');
        $calls = 0;

        $next = new HttpMiddlewareCallable(function (Request $_) use (&$calls): Response {
            $calls++;

            return new GenericResponse(Status::CREATED, ['id' => 'order-1']);
        });

        $middleware(
            new GenericRequest(
                Method::POST,
                '/orders',
                body: ['amount' => 100],
                headers: ['Idempotency-Key' => 'same-key'],
            ),
            $next,
        );

        $secondResponse = $middleware(
            new GenericRequest(
                Method::POST,
                '/orders',
                body: ['amount' => 101],
                headers: ['Idempotency-Key' => 'same-key'],
            ),
            $next,
        );

        $this->assertSame(Status::UNPROCESSABLE_CONTENT, $secondResponse->status);
        $this->assertSame(1, $calls);
    }

    #[Test]
    public function scopes_idempotency_keys_per_route_when_a_handler_has_multiple_routes(): void
    {
        $cache = new GenericCache(new ArrayAdapter());
        $config = new IdempotencyConfig();
        $keyResolver = new IdempotencyKeyResolver($config);
        $store = new CacheIdempotencyStore($cache, $keyResolver);

        $firstMiddleware = new IdempotencyMiddleware(
            cache: $cache,
            store: $store,
            keyResolver: $keyResolver,
            fingerprintGenerator: new RequestFingerprintGenerator(),
            config: $config,
            matchedRoute: $this->createMatchedRouteForUri('createForMultipleRoutes', '/bulk-orders'),
            processingOwner: new ProcessingOwner(),
            scopeResolver: new FixedScopeResolver(),
        );

        $secondMiddleware = new IdempotencyMiddleware(
            cache: $cache,
            store: $store,
            keyResolver: $keyResolver,
            fingerprintGenerator: new RequestFingerprintGenerator(),
            config: $config,
            matchedRoute: $this->createMatchedRouteForUri('createForMultipleRoutes', '/bulk-orders/import'),
            processingOwner: new ProcessingOwner(),
            scopeResolver: new FixedScopeResolver(),
        );

        $calls = 0;
        $next = new HttpMiddlewareCallable(function (Request $_) use (&$calls): Response {
            $calls++;

            return new GenericResponse(Status::CREATED, ['id' => 'order-' . $calls]);
        });

        $firstResponse = $firstMiddleware(
            new GenericRequest(
                Method::POST,
                '/bulk-orders',
                body: ['amount' => 100],
                headers: ['Idempotency-Key' => 'shared-key'],
            ),
            $next,
        );

        $secondResponse = $secondMiddleware(
            new GenericRequest(
                Method::POST,
                '/bulk-orders/import',
                body: ['amount' => 100],
                headers: ['Idempotency-Key' => 'shared-key'],
            ),
            $next,
        );

        $this->assertSame(Status::CREATED, $firstResponse->status);
        $this->assertSame(Status::CREATED, $secondResponse->status);
        $this->assertSame(2, $calls);
    }

    #[Test]
    public function isolates_idempotency_keys_per_scope_resolver_identity(): void
    {
        $cache = new GenericCache(new ArrayAdapter());
        $config = new IdempotencyConfig();
        $keyResolver = new IdempotencyKeyResolver($config);
        $store = new CacheIdempotencyStore($cache, $keyResolver);

        $userAMiddleware = new IdempotencyMiddleware(
            cache: $cache,
            store: $store,
            keyResolver: $keyResolver,
            fingerprintGenerator: new RequestFingerprintGenerator(),
            config: $config,
            matchedRoute: $this->createMatchedRoute('create'),
            processingOwner: new ProcessingOwner(),
            scopeResolver: new FixedScopeResolver('user-a'),
        );

        $userBMiddleware = new IdempotencyMiddleware(
            cache: $cache,
            store: $store,
            keyResolver: $keyResolver,
            fingerprintGenerator: new RequestFingerprintGenerator(),
            config: $config,
            matchedRoute: $this->createMatchedRoute('create'),
            processingOwner: new ProcessingOwner(),
            scopeResolver: new FixedScopeResolver('user-b'),
        );

        $calls = 0;
        $next = new HttpMiddlewareCallable(function (Request $_) use (&$calls): Response {
            $calls++;

            return new GenericResponse(Status::CREATED, ['id' => 'order-' . $calls]);
        });

        $request = new GenericRequest(
            Method::POST,
            '/orders',
            body: ['amount' => 100],
            headers: ['Idempotency-Key' => 'shared-key'],
        );

        $firstResponse = $userAMiddleware($request, $next);
        $secondResponse = $userBMiddleware($request, $next);

        $this->assertSame(Status::CREATED, $firstResponse->status);
        $this->assertSame(Status::CREATED, $secondResponse->status);
        $this->assertSame(2, $calls);
        $this->assertNull($firstResponse->getHeader('idempotency-replayed'));
        $this->assertNull($secondResponse->getHeader('idempotency-replayed'));
    }

    #[Test]
    public function can_skip_key_requirement_for_specific_routes(): void
    {
        $middleware = $this->createMiddleware('createWithoutKeyRequirement');
        $calls = 0;

        $response = $middleware(
            new GenericRequest(Method::POST, '/drafts', body: ['draft' => true]),
            new HttpMiddlewareCallable(function (Request $_) use (&$calls): Response {
                $calls++;

                return new GenericResponse(Status::CREATED, ['ok' => true]);
            }),
        );

        $this->assertSame(Status::CREATED, $response->status);
        $this->assertSame(1, $calls);
    }

    #[Test]
    public function idempotent_decorator_adds_the_idempotency_middleware(): void
    {
        $route = new FakeRoute();
        $attribute = new Idempotent();

        $attribute->decorate($route);

        $this->assertContains(IdempotencyMiddleware::class, $route->middleware);
    }

    #[Test]
    public function idempotent_decorator_does_not_add_duplicate_middleware_entries(): void
    {
        $route = new FakeRoute();
        $attribute = new Idempotent();

        $attribute->decorate($route);
        $attribute->decorate($route);

        $this->assertCount(
            1,
            array_filter(
                $route->middleware,
                static fn (string $middleware): bool => $middleware === IdempotencyMiddleware::class,
            ),
        );
    }

    #[Test]
    public function throws_for_non_post_and_patch_methods(): void
    {
        $middleware = $this->createMiddleware('create');

        $this->expectException(IdempotencyMethodWasNotSupported::class);

        $middleware(
            new GenericRequest(
                Method::PUT,
                '/orders',
                body: ['amount' => 100],
                headers: ['Idempotency-Key' => 'order-100'],
            ),
            new HttpMiddlewareCallable(static fn (Request $_): Response => new GenericResponse(Status::OK, ['ok' => true])),
        );
    }

    #[Test]
    public function uses_pending_ttl_for_lock_and_completion_ttl_for_pending_record(): void
    {
        $cache = new RecordingCache();
        $config = new IdempotencyConfig(ttlInSeconds: 120, pendingTtlInSeconds: 5);
        $keyResolver = new IdempotencyKeyResolver($config);
        $store = new RecordingStore($cache, $keyResolver);
        $middleware = new IdempotencyMiddleware(
            cache: $cache,
            store: $store,
            keyResolver: $keyResolver,
            fingerprintGenerator: new RequestFingerprintGenerator(),
            config: $config,
            matchedRoute: $this->createMatchedRoute('create'),
            processingOwner: new ProcessingOwner(),
            scopeResolver: new FixedScopeResolver(),
        );

        $response = $middleware(
            new GenericRequest(
                Method::POST,
                '/orders',
                body: ['amount' => 100],
                headers: ['Idempotency-Key' => 'order-ttl'],
            ),
            new HttpMiddlewareCallable(static fn (Request $_): Response => new GenericResponse(Status::CREATED, ['id' => 'order-1'])),
        );

        $this->assertSame(Status::CREATED, $response->status);
        $this->assertSame(5.0, $cache->lastLockDuration?->getTotalSeconds());
        $this->assertSame(120, $store->lastSavePending['ttlInSeconds'] ?? null);
        $this->assertIsString($store->lastSavePending['pendingOwner'] ?? null);
        $this->assertIsInt($store->lastSavePending['pendingHeartbeatAt'] ?? null);
    }

    #[Test]
    public function replays_a_non_serializable_body_as_fallback_text_instead_of_null(): void
    {
        $middleware = $this->createMiddleware('create');
        $calls = 0;

        $request = new GenericRequest(
            Method::POST,
            '/orders',
            body: ['amount' => 100],
            headers: ['Idempotency-Key' => 'non-serializable-body'],
        );

        $next = new HttpMiddlewareCallable(function (Request $_) use (&$calls): Response {
            $calls++;

            return new GenericResponse(
                Status::CREATED,
                (static function (): Generator {
                    yield 'chunk';
                })(),
            );
        });

        $middleware($request, $next);
        $replayedResponse = $middleware($request, $next);

        $this->assertSame(1, $calls);
        $this->assertSame(Status::CREATED, $replayedResponse->status);
        $this->assertNotNull($replayedResponse->body);
        $this->assertSame('true', $replayedResponse->getHeader('idempotency-replayed')?->first());
    }

    #[Test]
    public function replays_a_json_serializable_body(): void
    {
        $middleware = $this->createMiddleware('create');
        $calls = 0;

        $request = new GenericRequest(
            Method::POST,
            '/orders',
            body: ['amount' => 100],
            headers: ['Idempotency-Key' => 'json-serializable-body'],
        );

        $next = new HttpMiddlewareCallable(function (Request $_) use (&$calls): Response {
            $calls++;

            return new GenericResponse(Status::CREATED, new SerializableBody('order-1'));
        });

        $middleware($request, $next);
        $replayedResponse = $middleware($request, $next);

        $this->assertSame(1, $calls);
        $this->assertSame(Status::CREATED, $replayedResponse->status);
        $this->assertSame(['id' => 'order-1'], $replayedResponse->body);
        $this->assertSame('true', $replayedResponse->getHeader('idempotency-replayed')?->first());
    }

    #[Test]
    public function takes_over_a_pending_record_owned_by_a_dead_process(): void
    {
        $cache = new GenericCache(new ArrayAdapter());
        $config = new IdempotencyConfig();
        $keyResolver = new IdempotencyKeyResolver($config);
        $store = new CacheIdempotencyStore($cache, $keyResolver);
        $scopeResolver = new FixedScopeResolver();
        $middleware = new IdempotencyMiddleware(
            cache: $cache,
            store: $store,
            keyResolver: $keyResolver,
            fingerprintGenerator: new RequestFingerprintGenerator(),
            config: $config,
            matchedRoute: $this->createMatchedRoute('create'),
            processingOwner: new ProcessingOwner(),
            scopeResolver: $scopeResolver,
        );

        $request = new GenericRequest(
            Method::POST,
            '/orders',
            body: ['amount' => 100],
            headers: ['Idempotency-Key' => 'stale-order'],
        );

        $store->savePending(
            scope: sprintf('%s::%s:%s:%s:%s', IdempotencyTestController::class, 'create', Method::POST->value, '/orders', $scopeResolver->resolve($request)),
            key: 'stale-order',
            fingerprint: new RequestFingerprintGenerator()->generate($request),
            ttlInSeconds: 120,
            pendingOwner: sprintf('%s|%d|%s', php_uname('n'), 99_999_999, 'stale-owner'),
            pendingHeartbeatAt: time(),
        );

        $calls = 0;
        $response = $middleware(
            $request,
            new HttpMiddlewareCallable(function (Request $_) use (&$calls): Response {
                $calls++;

                return new GenericResponse(Status::CREATED, ['id' => 'order-1']);
            }),
        );

        $this->assertSame(1, $calls);
        $this->assertSame(Status::CREATED, $response->status);
    }

    #[Test]
    public function takes_over_a_pending_record_owned_by_another_host_when_the_heartbeat_is_stale(): void
    {
        $cache = new GenericCache(new ArrayAdapter());
        $config = new IdempotencyConfig();
        $keyResolver = new IdempotencyKeyResolver($config);
        $store = new CacheIdempotencyStore($cache, $keyResolver);
        $scopeResolver = new FixedScopeResolver();
        $middleware = new IdempotencyMiddleware(
            cache: $cache,
            store: $store,
            keyResolver: $keyResolver,
            fingerprintGenerator: new RequestFingerprintGenerator(),
            config: $config,
            matchedRoute: $this->createMatchedRoute('create'),
            processingOwner: new ProcessingOwner(),
            scopeResolver: $scopeResolver,
        );

        $request = new GenericRequest(
            Method::POST,
            '/orders',
            body: ['amount' => 100],
            headers: ['Idempotency-Key' => 'stale-order'],
        );

        $store->savePending(
            scope: sprintf('%s::%s:%s:%s:%s', IdempotencyTestController::class, 'create', Method::POST->value, '/orders', $scopeResolver->resolve($request)),
            key: 'stale-order',
            fingerprint: new RequestFingerprintGenerator()->generate($request),
            ttlInSeconds: 120,
            pendingOwner: 'remote-host|12345|stale-owner',
            pendingHeartbeatAt: time() - 120,
        );

        $calls = 0;
        $response = $middleware(
            $request,
            new HttpMiddlewareCallable(function (Request $_) use (&$calls): Response {
                $calls++;

                return new GenericResponse(Status::CREATED, ['id' => 'order-1']);
            }),
        );

        $this->assertSame(1, $calls);
        $this->assertSame(Status::CREATED, $response->status);
    }

    #[Test]
    public function does_not_take_over_a_pending_record_owned_by_another_host_when_the_heartbeat_is_fresh(): void
    {
        $cache = new GenericCache(new ArrayAdapter());
        $config = new IdempotencyConfig();
        $keyResolver = new IdempotencyKeyResolver($config);
        $store = new CacheIdempotencyStore($cache, $keyResolver);
        $scopeResolver = new FixedScopeResolver();
        $middleware = new IdempotencyMiddleware(
            cache: $cache,
            store: $store,
            keyResolver: $keyResolver,
            fingerprintGenerator: new RequestFingerprintGenerator(),
            config: $config,
            matchedRoute: $this->createMatchedRoute('create'),
            processingOwner: new ProcessingOwner(),
            scopeResolver: $scopeResolver,
        );

        $request = new GenericRequest(
            Method::POST,
            '/orders',
            body: ['amount' => 100],
            headers: ['Idempotency-Key' => 'live-order'],
        );

        $store->savePending(
            scope: sprintf('%s::%s:%s:%s:%s', IdempotencyTestController::class, 'create', Method::POST->value, '/orders', $scopeResolver->resolve($request)),
            key: 'live-order',
            fingerprint: new RequestFingerprintGenerator()->generate($request),
            ttlInSeconds: 120,
            pendingOwner: 'remote-host|12345|alive-owner',
            pendingHeartbeatAt: time(),
        );

        $calls = 0;
        $response = $middleware(
            $request,
            new HttpMiddlewareCallable(function (Request $_) use (&$calls): Response {
                $calls++;

                return new GenericResponse(Status::CREATED, ['id' => 'order-1']);
            }),
        );

        $this->assertSame(0, $calls);
        $this->assertSame(Status::CONFLICT, $response->status);
    }

    #[Test]
    public function does_not_delete_existing_completed_record_when_lookup_fails_before_pending_is_saved(): void
    {
        $cache = new GenericCache(new ArrayAdapter());
        $config = new IdempotencyConfig();
        $keyResolver = new IdempotencyKeyResolver($config);
        $baseStore = new CacheIdempotencyStore($cache, $keyResolver);
        $store = new ThrowingFindStore($baseStore);
        $scopeResolver = new FixedScopeResolver();

        $middleware = new IdempotencyMiddleware(
            cache: $cache,
            store: $store,
            keyResolver: $keyResolver,
            fingerprintGenerator: new RequestFingerprintGenerator(),
            config: $config,
            matchedRoute: $this->createMatchedRoute('create'),
            processingOwner: new ProcessingOwner(),
            scopeResolver: $scopeResolver,
        );

        $request = new GenericRequest(
            Method::POST,
            '/orders',
            body: ['amount' => 100],
            headers: ['Idempotency-Key' => 'order-123'],
        );

        $scope = sprintf('%s::%s:%s:%s:%s', IdempotencyTestController::class, 'create', Method::POST->value, '/orders', $scopeResolver->resolve($request));
        $fingerprint = new RequestFingerprintGenerator()->generate($request);

        $baseStore->saveCompleted(
            scope: $scope,
            key: 'order-123',
            fingerprint: $fingerprint,
            response: StoredResponse::fromResponse(new GenericResponse(Status::CREATED, ['id' => 'order-1'])),
            ttlInSeconds: 120,
        );

        try {
            $middleware(
                $request,
                new HttpMiddlewareCallable(static fn (Request $_): Response => new GenericResponse(Status::CREATED, ['id' => 'order-1'])),
            );

            $this->fail('Expected RuntimeException to be thrown.');
        } catch (RuntimeException) { // @mago-expect lint:no-empty-catch-clause
        }

        $this->assertFalse($store->deleteCalled);

        $record = $baseStore->find($scope, 'order-123');

        $this->assertNotNull($record);
        $this->assertSame(IdempotencyState::COMPLETED, $record->state);
    }

    private function createMiddleware(string $method, ?FixedScopeResolver $scopeResolver = null): IdempotencyMiddleware
    {
        $cache = new GenericCache(new ArrayAdapter());
        $config = new IdempotencyConfig();
        $resolver = new IdempotencyKeyResolver($config);

        return new IdempotencyMiddleware(
            cache: $cache,
            store: new CacheIdempotencyStore($cache, $resolver),
            keyResolver: $resolver,
            fingerprintGenerator: new RequestFingerprintGenerator(),
            config: $config,
            matchedRoute: $this->createMatchedRoute($method),
            processingOwner: new ProcessingOwner(),
            scopeResolver: $scopeResolver ?? new FixedScopeResolver(),
        );
    }

    private function createMatchedRoute(string $method): MatchedRoute
    {
        $methodReflector = MethodReflector::fromParts(IdempotencyTestController::class, $method);
        $route = $methodReflector->getAttribute(Route::class);

        if ($route === null) {
            throw new RuntimeException(sprintf('No route found for `%s`.', $method));
        }

        return $this->createMatchedRouteFromRoute($methodReflector, $route);
    }

    private function createMatchedRouteForUri(string $method, string $uri): MatchedRoute
    {
        $methodReflector = MethodReflector::fromParts(IdempotencyTestController::class, $method);
        $route = array_find(
            $methodReflector->getAttributes(Route::class),
            static fn (Route $route): bool => $route->uri === $uri,
        );

        if ($route === null) {
            throw new RuntimeException(sprintf('No route `%s` found for `%s`.', $uri, $method));
        }

        return $this->createMatchedRouteFromRoute($methodReflector, $route);
    }

    private function createMatchedRouteFromRoute(MethodReflector $methodReflector, Route $route): MatchedRoute
    {
        $decorators = [
            ...$methodReflector->getAttributes(RouteDecorator::class),
            ...$methodReflector->getDeclaringClass()->getAttributes(RouteDecorator::class),
        ];

        return new MatchedRoute(
            route: DiscoveredRoute::fromRoute($route, $decorators, $methodReflector),
            params: [],
        );
    }
}

final class ThrowingFindStore implements IdempotencyStore
{
    public bool $deleteCalled = false;

    public function __construct(
        private readonly IdempotencyStore $store,
    ) {}

    public function find(string $scope, string $key): ?IdempotencyRecord
    {
        throw new RuntimeException('Simulated store read failure.');
    }

    public function savePending(string $scope, string $key, string $fingerprint, int $ttlInSeconds, ?string $pendingOwner = null, ?int $pendingHeartbeatAt = null): void
    {
        $this->store->savePending($scope, $key, $fingerprint, $ttlInSeconds, $pendingOwner, $pendingHeartbeatAt);
    }

    public function updateHeartbeat(string $scope, string $key, string $owner, int $heartbeatAt, int $ttlInSeconds): void
    {
        $this->store->updateHeartbeat($scope, $key, $owner, $heartbeatAt, $ttlInSeconds);
    }

    public function saveCompleted(string $scope, string $key, string $fingerprint, ?StoredResponse $response, int $ttlInSeconds): void
    {
        $this->store->saveCompleted($scope, $key, $fingerprint, $response, $ttlInSeconds);
    }

    public function delete(string $scope, string $key): void
    {
        $this->deleteCalled = true;

        $this->store->delete($scope, $key);
    }
}

final class FakeRoute implements Route
{
    public Method $method = Method::POST;

    public string $uri = '/orders';

    public array $middleware = [];

    public array $without = [];
}

final class IdempotencyTestController
{
    #[Post('/orders')]
    #[Idempotent]
    public function create(): Response
    {
        return new GenericResponse(Status::CREATED, ['id' => 'order-1']);
    }

    #[Post('/drafts')]
    #[Idempotent]
    #[IdempotentRoute(requireKey: false)]
    public function createWithoutKeyRequirement(): Response
    {
        return new GenericResponse(Status::CREATED, ['ok' => true]);
    }

    #[Post('/bulk-orders')]
    #[Post('/bulk-orders/import')]
    #[Idempotent]
    public function createForMultipleRoutes(): Response
    {
        return new GenericResponse(Status::CREATED, ['id' => 'bulk-order-1']);
    }
}

final readonly class SerializableBody implements JsonSerializable
{
    public function __construct(
        private string $id,
    ) {}

    public function jsonSerialize(): array
    {
        return ['id' => $this->id];
    }
}
