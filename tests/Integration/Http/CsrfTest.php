<?php

namespace Tests\Tempest\Integration\Http;

use PHPUnit\Framework\Attributes\TestWith;
use Tempest\Core\AppConfig;
use Tempest\Core\Environment;
use Tempest\Http\Cookie\Cookie;
use Tempest\Http\GenericRequest;
use Tempest\Http\Method;
use Tempest\Http\Session\CsrfTokenDidNotMatch;
use Tempest\Http\Session\Session;
use Tempest\Http\Session\VerifyCsrfMiddleware;
use Tempest\View\ViewCache;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Http\csrf_token;

final class CsrfTest extends FrameworkIntegrationTestCase
{
    public function test_csrf_is_sent_as_cookie(): void
    {
        $this->container->get(AppConfig::class)->environment = Environment::PRODUCTION;

        $token = $this->container->get(Session::class)->get(Session::CSRF_TOKEN_KEY);

        $this->http
            ->get('/test')
            ->assertHasCookie(VerifyCsrfMiddleware::CSRF_COOKIE_KEY, fn (string $value) => $value === $token); // @mago-expect security/no-insecure-comparison
    }

    #[TestWith([Method::POST])]
    #[TestWith([Method::PUT])]
    #[TestWith([Method::PATCH])]
    #[TestWith([Method::DELETE])]
    public function test_throws_when_missing_in_write_verbs(Method $method): void
    {
        $this->expectException(CsrfTokenDidNotMatch::class);

        $this->container->get(AppConfig::class)->environment = Environment::PRODUCTION;
        $this->http->sendRequest(new GenericRequest($method, uri: '/test'));
    }

    #[TestWith([Method::GET])]
    #[TestWith([Method::OPTIONS])]
    #[TestWith([Method::HEAD])]
    public function test_allows_missing_in_read_verbs(Method $method): void
    {
        $this->container->get(AppConfig::class)->environment = Environment::PRODUCTION;

        $this->http
            ->sendRequest(new GenericRequest($method, uri: '/test'))
            ->assertOk();
    }

    public function test_throws_when_mismatch_from_body(): void
    {
        $this->expectException(CsrfTokenDidNotMatch::class);

        $this->container->get(AppConfig::class)->environment = Environment::PRODUCTION;
        $this->container->get(Session::class)->set(Session::CSRF_TOKEN_KEY, 'abc');

        $this->http->post('/test', [Session::CSRF_TOKEN_KEY => 'def']);
    }

    public function test_throws_when_mismatch_from_header(): void
    {
        $this->expectException(CsrfTokenDidNotMatch::class);

        $this->container->get(AppConfig::class)->environment = Environment::PRODUCTION;
        $this->container->get(Session::class)->set(Session::CSRF_TOKEN_KEY, 'abc');

        $this->http->post('/test', [Session::CSRF_TOKEN_KEY => 'def']);
    }

    public function test_matches_from_body(): void
    {
        $this->container->get(AppConfig::class)->environment = Environment::PRODUCTION;

        $session = $this->container->get(Session::class);

        $this->http
            ->post('/test', [Session::CSRF_TOKEN_KEY => $session->token])
            ->assertOk();
    }

    public function test_matches_from_header(): void
    {
        $this->container->get(AppConfig::class)->environment = Environment::PRODUCTION;

        $session = $this->container->get(Session::class);

        $this->http
            ->post('/test', headers: [VerifyCsrfMiddleware::CSRF_HEADER_KEY => $session->token])
            ->assertOk();
    }

    public function test_csrf_component(): void
    {
        $session = $this->container->get(Session::class);
        $session->set(Session::CSRF_TOKEN_KEY, 'test');

        $rendered = $this->render(<<<HTML
        <x-csrf-token />
        HTML);

        $this->assertSame(
            '<input type="hidden" name="#csrf_token" value="test">',
            $rendered,
        );
    }

    public function test_csrf_token_function(): void
    {
        $session = $this->container->get(Session::class);
        $session->set(Session::CSRF_TOKEN_KEY, 'test');

        $this->assertSame('test', csrf_token());
    }

    public function test_csrf_with_cached_view(): void
    {
        $this->get(ViewCache::class)->enabled = true;

        $oldVersion = $this->render(<<<HTML
        <x-csrf-token />
        HTML);

        $session = $this->container->get(Session::class);
        $session->destroy();

        $newVersion = $this->render(<<<HTML
        <x-csrf-token />
        HTML);

        $this->assertNotSame($oldVersion, $newVersion);
    }
}
