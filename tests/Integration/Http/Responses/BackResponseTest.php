<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http\Responses;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Http\GenericRequest;
use Tempest\Http\Header;
use Tempest\Http\Method;
use Tempest\Http\Request;
use Tempest\Http\Responses\Back;
use Tempest\Http\Session\PreviousUrl;
use Tempest\Http\Status;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class BackResponseTest extends FrameworkIntegrationTestCase
{
    private PreviousUrl $previousUrl {
        get => $this->container->get(PreviousUrl::class);
    }

    #[Test]
    public function back_response_with_no_previous_url(): void
    {
        $response = new Back();

        $this->assertSame(Status::FOUND, $response->status);
        $this->assertEquals(new Header('Location', ['/']), $response->headers['Location']);
    }

    #[Test]
    public function back_response_with_tracked_url(): void
    {
        $this->previousUrl->track(
            request: new GenericRequest(method: Method::GET, uri: '/previous-page'),
        );

        $this->assertEquals(
            new Header('Location', ['/previous-page']),
            new Back()->headers['Location'],
        );
    }

    #[Test]
    public function back_response_with_fallback(): void
    {
        $this->assertEquals(
            new Header('Location', ['/fallback-url']),
            new Back('/fallback-url')->headers['Location'],
        );
    }

    #[Test]
    public function back_response_prefers_tracked_url_over_fallback(): void
    {
        $this->previousUrl->track(
            request: new GenericRequest(method: Method::GET, uri: '/tracked-page'),
        );

        $this->assertEquals(
            new Header('Location', ['/tracked-page']),
            new Back('/fallback-url')->headers['Location'],
        );
    }

    #[Test]
    public function back_response_with_referer_header(): void
    {
        $this->container->singleton(Request::class, new GenericRequest(
            method: Method::GET,
            uri: '/current-page',
            headers: ['referer' => '/referer-page'],
        ));

        $this->assertEquals(
            new Header('Location', ['/referer-page']),
            new Back()->headers['Location'],
        );
    }

    #[Test]
    public function back_response_prefers_tracked_url_over_referer(): void
    {
        $this->previousUrl->track(new GenericRequest(
            method: Method::GET,
            uri: '/tracked-page',
            headers: ['referer' => '/referer-page'],
        ));

        $this->container->singleton(Request::class, new GenericRequest(
            method: Method::GET,
            uri: '/current-page',
            headers: ['referer' => '/referer-page'],
        ));

        $this->assertEquals(
            new Header('Location', ['/tracked-page']),
            new Back()->headers['Location'],
        );
    }

    #[Test]
    public function back_response_for_get_request(): void
    {
        $this->http
            ->get('/test-redirect-back-url')
            ->assertRedirect('/test-redirect-back-url');
    }
}
