<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Router;

use PHPUnit\Framework\Attributes\PreCondition;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Router\PreventCrossSiteRequestsMiddleware;
use Tempest\Router\RouteConfig;
use Tempest\Router\SecFetchMode;
use Tempest\Router\SecFetchSite;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class PreventCrossSiteRequestsMiddlewareTest extends FrameworkIntegrationTestCase
{
    #[PreCondition]
    protected function configure(): void
    {
        $this
            ->container->get(RouteConfig::class)
            ->middleware->add(PreventCrossSiteRequestsMiddleware::class);
    }

    #[Test]
    public function safe_methods_are_always_allowed(): void
    {
        $this->http->get('/test')->assertOk();
        $this->http->head('/test')->assertOk();
        $this->http->options('/test')->assertOk();
    }

    #[Test]
    public function post_with_same_origin_is_allowed(): void
    {
        $this->http
            ->post('/test', headers: [
                'sec-fetch-site' => SecFetchSite::SAME_ORIGIN,
                'sec-fetch-mode' => SecFetchMode::CORS,
            ])
            ->assertOk();
    }

    #[Test]
    public function post_with_same_site_is_allowed(): void
    {
        $this->http
            ->post('/test', headers: [
                'sec-fetch-site' => SecFetchSite::SAME_SITE,
                'sec-fetch-mode' => SecFetchMode::CORS,
            ])
            ->assertOk();
    }

    #[Test]
    public function post_with_none_site_is_allowed(): void
    {
        $this->http
            ->post('/test', headers: [
                'sec-fetch-site' => SecFetchSite::NONE,
                'sec-fetch-mode' => SecFetchMode::NAVIGATE,
            ])
            ->assertOk();
    }

    #[Test]
    public function post_with_cross_site_navigation_is_allowed(): void
    {
        $this->http
            ->post('/test', headers: [
                'sec-fetch-site' => SecFetchSite::CROSS_SITE,
                'sec-fetch-mode' => SecFetchMode::NAVIGATE,
            ])
            ->assertOk();
    }

    #[Test]
    public function post_with_cross_site_cors_is_blocked(): void
    {
        $this->http
            ->post('/test', headers: [
                'sec-fetch-site' => SecFetchSite::CROSS_SITE,
                'sec-fetch-mode' => SecFetchMode::CORS,
            ])
            ->assertForbidden();
    }

    #[Test]
    public function put_with_cross_site_cors_is_blocked(): void
    {
        $this->http
            ->put('/test', headers: [
                'sec-fetch-site' => SecFetchSite::CROSS_SITE,
                'sec-fetch-mode' => SecFetchMode::CORS,
            ])
            ->assertForbidden();
    }

    #[Test]
    public function patch_with_cross_site_cors_is_blocked(): void
    {
        $this->http
            ->patch('/test', headers: [
                'sec-fetch-site' => SecFetchSite::CROSS_SITE,
                'sec-fetch-mode' => SecFetchMode::CORS,
            ])
            ->assertForbidden();
    }

    #[Test]
    public function delete_with_cross_site_cors_is_blocked(): void
    {
        $this->http
            ->delete('/test', headers: [
                'sec-fetch-site' => SecFetchSite::CROSS_SITE,
                'sec-fetch-mode' => SecFetchMode::CORS,
            ])
            ->assertForbidden();
    }

    #[Test]
    public function post_without_sec_fetch_headers_is_blocked(): void
    {
        $this->http
            ->withoutSecFetchHeaders()
            ->post('/test')
            ->assertForbidden();
    }

    #[Test]
    public function post_with_cross_site_no_cors_is_blocked(): void
    {
        $this->http
            ->post('/test', headers: [
                'sec-fetch-site' => SecFetchSite::CROSS_SITE,
                'sec-fetch-mode' => SecFetchMode::NO_CORS,
            ])
            ->assertForbidden();
    }

    #[Test]
    public function post_with_cross_site_websocket_is_blocked(): void
    {
        $this->http
            ->post('/test', headers: [
                'sec-fetch-site' => SecFetchSite::CROSS_SITE,
                'sec-fetch-mode' => SecFetchMode::WEBSOCKET,
            ])
            ->assertForbidden();
    }

    #[Test]
    public function delete_with_same_origin_is_allowed(): void
    {
        $this->http
            ->delete('/test', headers: [
                'sec-fetch-site' => SecFetchSite::SAME_ORIGIN,
                'sec-fetch-mode' => SecFetchMode::CORS,
            ])
            ->assertOk();
    }

    #[Test]
    public function put_with_same_site_is_allowed(): void
    {
        $this->http
            ->put('/test', headers: [
                'sec-fetch-site' => SecFetchSite::SAME_SITE,
                'sec-fetch-mode' => SecFetchMode::CORS,
            ])
            ->assertOk();
    }

    #[Test]
    public function patch_with_none_site_is_allowed(): void
    {
        $this->http
            ->patch('/test', headers: [
                'sec-fetch-site' => SecFetchSite::NONE,
                'sec-fetch-mode' => SecFetchMode::NAVIGATE,
            ])
            ->assertOk();
    }
}
