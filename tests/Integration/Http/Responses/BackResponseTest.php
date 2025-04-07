<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http\Responses;

use Tempest\Http\Method;
use Tempest\Http\Status;
use Tempest\Router\GenericRequest;
use Tempest\Router\Header;
use Tempest\Router\Request;
use Tempest\Router\Responses\Back;
use Tempest\Router\Session\Session;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class BackResponseTest extends FrameworkIntegrationTestCase
{
    public function test_back_response(): void
    {
        $this->bindRequest();
        $response = new Back();

        $this->assertSame(Status::FOUND, $response->status);
        $this->assertEquals(new Header('Location', ['/']), $response->headers['Location']);
        $this->assertNotSame(Status::OK, $response->status);
    }

    public function test_back_response_with_referer(): void
    {
        $this->bindRequest(referer: $referer = '/referer-test');

        $response = new Back();

        $this->assertEquals(new Header('Location', [$referer]), $response->headers['Location']);
    }

    public function test_back_response_with_fallback(): void
    {
        $this->bindRequest();

        $referer = '/test';
        $response = new Back($referer);

        $this->assertEquals(new Header('Location', [$referer]), $response->headers['Location']);
    }

    public function test_back_response_for_get_request(): void
    {
        $this->http
            ->get('/test-redirect-back-url')
            ->assertRedirect('/test-redirect-back-url');
    }

    public function bindRequest(?string $uri = '/', ?string $referer = null): void
    {
        $headers = $referer ? ['referer' => $referer] : [];

        $this->container->singleton(Request::class, new GenericRequest(
            method: Method::GET,
            uri: $uri,
            headers: $headers,
        ));
    }
}
