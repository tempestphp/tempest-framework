<?php

namespace Tests\Tempest\Integration\Http;

use PHPUnit\Framework\Attributes\TestWith;
use Tempest\Core\Environment;
use Tempest\Cryptography\Encryption\Encrypter;
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
        $this->container->singleton(Environment::class, Environment::PRODUCTION);

        $token = $this->container->get(Session::class)->get(Session::CSRF_TOKEN_KEY);

        $this->http
            ->get('/test')
            ->assertHasCookie(VerifyCsrfMiddleware::CSRF_COOKIE_KEY, fn (string $value) => $value === $token); // @mago-expect lint:no-insecure-comparison
    }

    #[TestWith([Method::POST])]
    #[TestWith([Method::PUT])]
    #[TestWith([Method::PATCH])]
    #[TestWith([Method::DELETE])]
    public function test_throws_when_missing_in_write_verbs(Method $method): void
    {
        $this->expectException(CsrfTokenDidNotMatch::class);

        $this->container->singleton(Environment::class, Environment::PRODUCTION);
        $this->http->sendRequest(new GenericRequest($method, uri: '/test'));
    }

    #[TestWith([Method::GET])]
    #[TestWith([Method::OPTIONS])]
    #[TestWith([Method::HEAD])]
    public function test_allows_missing_in_read_verbs(Method $method): void
    {
        $this->container->singleton(Environment::class, Environment::PRODUCTION);

        $this->http
            ->sendRequest(new GenericRequest($method, uri: '/test'))
            ->assertOk();
    }

    public function test_throws_when_mismatch_from_body(): void
    {
        $this->expectException(CsrfTokenDidNotMatch::class);

        $this->container->singleton(Environment::class, Environment::PRODUCTION);
        $this->container->get(Session::class)->set(Session::CSRF_TOKEN_KEY, 'abc');

        $this->http->post('/test', [Session::CSRF_TOKEN_KEY => 'def']);
    }

    public function test_throws_when_mismatch_from_header(): void
    {
        $this->expectException(CsrfTokenDidNotMatch::class);

        $this->container->singleton(Environment::class, Environment::PRODUCTION);
        $this->container->get(Session::class)->set(Session::CSRF_TOKEN_KEY, 'abc');

        $this->http->post('/test', [Session::CSRF_TOKEN_KEY => 'def']);
    }

    public function test_matches_from_body(): void
    {
        $this->container->singleton(Environment::class, Environment::PRODUCTION);

        $session = $this->container->get(Session::class);

        $this->http
            ->post('/test', [Session::CSRF_TOKEN_KEY => $session->token])
            ->assertOk();
    }

    public function test_matches_from_header_when_encrypted(): void
    {
        $this->container->singleton(Environment::class, Environment::PRODUCTION);
        $session = $this->container->get(Session::class);

        // Encrypt the token as it would be in a real request
        $sessionCookieValue = $this->container
            ->get(Encrypter::class)
            ->encrypt($session->token)
            ->serialize();

        $this->http
            ->post('/test', headers: [VerifyCsrfMiddleware::CSRF_HEADER_KEY => $sessionCookieValue])
            ->assertOk();
    }

    public function test_throws_csrf_exception_when_header_is_non_serialized_hash(): void
    {
        $this->expectException(CsrfTokenDidNotMatch::class);
        $this->container->singleton(Environment::class, Environment::PRODUCTION);
        $session = $this->container->get(Session::class);

        // simulate a non-serialized hash
        $sessionCookieValue = 'i-am-not-correct';

        $this->http
            ->post('/test', headers: [VerifyCsrfMiddleware::CSRF_HEADER_KEY => $sessionCookieValue]);
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
