<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Http\GenericRequest;
use Tempest\Http\GenericResponse;
use Tempest\Http\Method;
use Tempest\Http\Session\PreviousUrl;
use Tempest\Http\Session\TrackPreviousUrlMiddleware;
use Tempest\Http\Status;
use Tempest\Router\HttpMiddlewareCallable;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class TrackPreviousUrlMiddlewareTest extends FrameworkIntegrationTestCase
{
    private PreviousUrl $previousUrl {
        get => $this->container->get(PreviousUrl::class);
    }

    private TrackPreviousUrlMiddleware $middleware {
        get => new TrackPreviousUrlMiddleware($this->previousUrl);
    }

    #[Test]
    public function middleware_tracks_request_url(): void
    {
        $this->middleware->__invoke(
            request: new GenericRequest(method: Method::GET, uri: '/dashboard'),
            next: new HttpMiddlewareCallable(fn () => new GenericResponse(Status::OK)),
        );

        $this->assertEquals('/dashboard', $this->previousUrl->get());
    }

    #[Test]
    public function middleware_calls_next_handler(): void
    {
        $expected = new GenericResponse(Status::OK, body: 'Test response');

        $response = $this->middleware->__invoke(
            request: new GenericRequest(method: Method::GET, uri: '/test'),
            next: new HttpMiddlewareCallable(fn () => $expected),
        );

        $this->assertSame($expected, $response);
    }

    #[Test]
    public function middleware_does_not_track_post_requests(): void
    {
        $this->middleware->__invoke(
            request: new GenericRequest(method: Method::POST, uri: '/form-submit'),
            next: new HttpMiddlewareCallable(fn () => new GenericResponse(Status::OK)),
        );

        $this->assertEquals('/', $this->previousUrl->get());
    }

    #[Test]
    public function middleware_tracks_multiple_requests_in_sequence(): void
    {
        $next = new HttpMiddlewareCallable(fn () => new GenericResponse(Status::OK));

        $this->middleware->__invoke(
            request: new GenericRequest(method: Method::GET, uri: '/page1'),
            next: $next,
        );

        $this->assertEquals('/page1', $this->previousUrl->get());

        $this->middleware->__invoke(
            request: new GenericRequest(method: Method::GET, uri: '/page2'),
            next: $next,
        );

        $this->assertEquals('/page2', $this->previousUrl->get());

        $this->middleware->__invoke(
            request: new GenericRequest(method: Method::GET, uri: '/page3'),
            next: $next,
        );

        $this->assertEquals('/page3', $this->previousUrl->get());
    }

    #[Test]
    public function middleware_ignores_ajax_requests(): void
    {
        $this->middleware->__invoke(
            request: new GenericRequest(method: Method::GET, uri: '/dashboard'),
            next: new HttpMiddlewareCallable(fn () => new GenericResponse(Status::OK)),
        );

        $this->middleware->__invoke(
            request: new GenericRequest(
                method: Method::GET,
                uri: '/api/data',
                headers: ['X-Requested-With' => 'XMLHttpRequest'],
            ),
            next: new HttpMiddlewareCallable(fn () => new GenericResponse(Status::OK)),
        );

        $this->assertEquals('/dashboard', $this->previousUrl->get());
    }
}
