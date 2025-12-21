<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Http\GenericRequest;
use Tempest\Http\Method;
use Tempest\Http\Session\PreviousUrl;
use Tempest\Http\Session\Session;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class PreviousUrlTest extends FrameworkIntegrationTestCase
{
    private PreviousUrl $tracker {
        get => $this->container->get(PreviousUrl::class);
    }

    private Session $session {
        get => $this->container->get(Session::class);
    }

    #[Test]
    public function tracks_get_requests(): void
    {
        $this->tracker->track(new GenericRequest(
            method: Method::GET,
            uri: '/dashboard',
        ));

        $this->assertEquals('/dashboard', $this->tracker->get());
    }

    #[Test]
    public function does_not_track_post_requests(): void
    {
        $this->tracker->track(new GenericRequest(
            method: Method::POST,
            uri: '/submit-form',
        ));

        $this->assertEquals('/', $this->tracker->get());
    }

    #[Test]
    public function does_not_track_ajax_requests(): void
    {
        $this->tracker->track(new GenericRequest(
            method: Method::GET,
            uri: '/api/data',
            headers: ['X-Requested-With' => 'XMLHttpRequest'],
        ));

        $this->assertEquals('/', $this->tracker->get());
    }

    #[Test]
    public function does_not_track_prefetch_requests(): void
    {
        $this->tracker->track(new GenericRequest(
            method: Method::GET,
            uri: '/prefetch-page',
            headers: ['Purpose' => 'prefetch'],
        ));

        $this->assertEquals('/', $this->tracker->get());
    }

    #[Test]
    public function get_returns_default_when_no_previous_url(): void
    {
        $this->assertEquals('/', $this->tracker->get());
        $this->assertEquals('/home', $this->tracker->get('/home'));
    }

    #[Test]
    public function updates_previous_url_on_subsequent_tracks(): void
    {
        $this->tracker->track(new GenericRequest(method: Method::GET, uri: '/page1'));
        $this->assertEquals('/page1', $this->tracker->get());

        $this->tracker->track(new GenericRequest(method: Method::GET, uri: '/page2'));
        $this->assertEquals('/page2', $this->tracker->get());

        $this->tracker->track(new GenericRequest(method: Method::GET, uri: '/page3'));
        $this->assertEquals('/page3', $this->tracker->get());
    }

    #[Test]
    public function set_intended_stores_url(): void
    {
        $this->tracker->setIntended('/protected-page');

        $this->assertEquals('/protected-page', $this->session->get('#intended_url'));
    }

    #[Test]
    public function get_intended_returns_and_removes_url(): void
    {
        $this->tracker->setIntended('/admin/dashboard');

        $this->assertEquals('/admin/dashboard', $this->tracker->getIntended());
        $this->assertEquals('/', $this->tracker->getIntended());
    }

    #[Test]
    public function get_intended_returns_default_when_not_set(): void
    {
        $this->assertEquals('/', $this->tracker->getIntended());
        $this->assertEquals('/fallback', $this->tracker->getIntended('/fallback'));
    }

    #[Test]
    public function tracks_urls_with_query_strings(): void
    {
        $this->tracker->track(new GenericRequest(
            method: Method::GET,
            uri: '/search?q=tempest&filter=docs',
        ));

        $this->assertEquals('/search?q=tempest&filter=docs', $this->tracker->get());
    }

    #[Test]
    public function tracks_urls_with_fragments(): void
    {
        $this->tracker->track(new GenericRequest(
            method: Method::GET,
            uri: '/docs#installation',
        ));

        $this->assertEquals('/docs#installation', $this->tracker->get());
    }
}
