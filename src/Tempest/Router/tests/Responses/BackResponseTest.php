<?php

declare(strict_types=1);

namespace Tempest\Router\Tests\Responses;

use Tempest\Http\Method;
use Tempest\Http\Status;
use Tempest\Router\GenericRequest;
use Tempest\Router\Header;
use Tempest\Router\Request;
use Tempest\Router\Responses\Back;
use Tests\Tempest\Integration\IntegrationTestCase;

/**
 * @internal
 */
final class BackResponseTest extends IntegrationTestCase
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
        $this->bindRequest($url = '/referer-test');

        $response = new Back();

        $this->assertEquals(new Header('Location', [$url]), $response->headers['Location']);
    }

    public function test_back_response_with_fallback(): void
    {
        $this->bindRequest();

        $url = '/test';
        $response = new Back($url);

        $this->assertEquals(new Header('Location', [$url]), $response->headers['Location']);
    }

    public function bindRequest(?string $url = null): void
    {
        $headers = $url ? ['referer' => $url] : [];
        $this->container->singleton(Request::class, new GenericRequest(
            method: Method::GET,
            uri: '/',
            headers: $headers,
        ));
    }
}
