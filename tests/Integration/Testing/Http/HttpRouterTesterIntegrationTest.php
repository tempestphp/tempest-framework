<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Testing\Http;

use Exception;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Http\Request;
use Tempest\Http\Responses\Ok;
use Tempest\Router\Post;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Route\Fixtures\Http500Controller;

/**
 * @internal
 */
final class HttpRouterTesterIntegrationTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function get_requests(): void
    {
        $this->http
            ->get('/test')
            ->assertOk();
    }

    #[Test]
    public function get_requests_failure(): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->http
            ->get('/this-route-does-not-exist')
            ->assertOk();
    }

    #[Test]
    public function head_requests(): void
    {
        $this->http
            ->head('/test')
            ->assertOk();
    }

    #[Test]
    public function head_requests_failure(): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->http
            ->head('/this-route-does-not-exist')
            ->assertOk();
    }

    #[Test]
    public function post_requests(): void
    {
        $this->http
            ->post('/test')
            ->assertOk();
    }

    #[Test]
    public function post_requests_failure(): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->http
            ->post('/this-route-does-not-exist')
            ->assertOk();
    }

    #[Test]
    public function put_requests(): void
    {
        $this->http
            ->put('/test')
            ->assertOk();
    }

    #[Test]
    public function put_requests_failure(): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->http
            ->put('/this-route-does-not-exist')
            ->assertOk();
    }

    #[Test]
    public function delete_requests(): void
    {
        $this->http
            ->delete('/test')
            ->assertOk();
    }

    #[Test]
    public function delete_requests_failure(): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->http
            ->delete('/this-route-does-not-exist')
            ->assertOk();
    }

    #[Test]
    public function connect_requests(): void
    {
        $this->http
            ->connect('/test')
            ->assertOk();
    }

    #[Test]
    public function connect_requests_failure(): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->http
            ->connect('/this-route-does-not-exist')
            ->assertOk();
    }

    #[Test]
    public function options_requests(): void
    {
        $this->http
            ->options('/test')
            ->assertOk();
    }

    #[Test]
    public function options_requests_failure(): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->http
            ->options('/this-route-does-not-exist')
            ->assertOk();
    }

    #[Test]
    public function trace_requests(): void
    {
        $this->http
            ->trace('/test')
            ->assertOk();
    }

    #[Test]
    public function trace_requests_failure(): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->http
            ->trace('/this-route-does-not-exist')
            ->assertOk();
    }

    #[Test]
    public function patch_requests(): void
    {
        $this->http
            ->patch('/test')
            ->assertOk();
    }

    #[Test]
    public function patch_requests_failure(): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->http
            ->patch('/this-route-does-not-exist')
            ->assertOk();
    }

    #[Test]
    public function has_exception(): void
    {
        $this->http->registerRoute([Http500Controller::class, 'throwsException']);

        $response = $this->http
            ->get('/throws-exception')
            ->assertServerError();

        $this->assertInstanceOf(Exception::class, $response->throwable);
    }

    #[Test]
    public function query(): void
    {
        $this->assertSame($this->http->get('/test?foo=baz', query: ['foo' => 'bar'])->request->uri, '/test?foo=bar');
        $this->assertSame($this->http->get('/test?jon=doe', query: ['foo' => 'bar'])->request->uri, '/test?jon=doe&foo=bar');
        $this->assertSame($this->http->get('/test?jon=doe', query: ['foo' => ['bar' => 'baz']])->request->uri, '/test?jon=doe&foo%5Bbar%5D=baz');

        $this->assertSame($this->http->get('/test', query: ['foo' => 'bar'])->request->uri, '/test?foo=bar');
        $this->assertSame($this->http->post('/test', query: ['foo' => 'bar'])->request->uri, '/test?foo=bar');
        $this->assertSame($this->http->put('/test', query: ['foo' => 'bar'])->request->uri, '/test?foo=bar');
        $this->assertSame($this->http->delete('/test', query: ['foo' => 'bar'])->request->uri, '/test?foo=bar');
        $this->assertSame($this->http->patch('/test', query: ['foo' => 'bar'])->request->uri, '/test?foo=bar');
        $this->assertSame($this->http->head('/test', query: ['foo' => 'bar'])->request->uri, '/test?foo=bar');
    }

    #[Test]
    public function raw_body_string(): void
    {
        $this->http->registerRoute([TestController::class, 'handleRawBody']);

        $response = $this->http
            ->post('/raw-body', body: 'ok')
            ->assertOk();

        $this->assertSame('ok', $response->body);
        $this->assertSame('ok', $response->request->raw);
        $this->assertSame([], $response->request->body);
    }
}

final class TestController
{
    #[Post('/raw-body')]
    public function handleRawBody(Request $request): Ok
    {
        return new Ok($request->raw ?? '');
    }
}
