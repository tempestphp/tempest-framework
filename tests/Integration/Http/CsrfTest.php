<?php

namespace Tests\Tempest\Integration\Http;

use PHPUnit\Framework\Attributes\PreCondition;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use Tempest\Core\Environment;
use Tempest\Cryptography\Encryption\Encrypter;
use Tempest\Http\GenericRequest;
use Tempest\Http\Method;
use Tempest\Http\Session\Session;
use Tempest\Http\Session\VerifyCsrfMiddleware;
use Tempest\Http\Status;
use Tempest\View\ViewCache;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Http\csrf_token;

final class CsrfTest extends FrameworkIntegrationTestCase
{
    #[PreCondition]
    protected function configureEnvironment(): void
    {
        $this->container->singleton(Environment::class, Environment::PRODUCTION);
    }

    #[Test]
    public function csrf_is_sent_as_cookie(): void
    {
        $token = $this->container->get(Session::class)->get(Session::CSRF_TOKEN_KEY);

        $this->http
            ->get('/test')
            ->assertHasCookie(VerifyCsrfMiddleware::CSRF_COOKIE_KEY, fn (string $value) => $value === $token); // @mago-expect lint:no-insecure-comparison
    }

    #[TestWith([Method::POST])]
    #[TestWith([Method::PUT])]
    #[TestWith([Method::PATCH])]
    #[TestWith([Method::DELETE])]
    #[Test]
    public function throws_when_missing_in_write_verbs(Method $method): void
    {
        $this->http
            ->sendRequest(new GenericRequest($method, uri: '/test'))
            ->assertStatus(Status::UNPROCESSABLE_CONTENT);
    }

    #[TestWith([Method::GET])]
    #[TestWith([Method::OPTIONS])]
    #[TestWith([Method::HEAD])]
    #[Test]
    public function allows_missing_in_read_verbs(Method $method): void
    {
        $this->http
            ->sendRequest(new GenericRequest($method, uri: '/test'))
            ->assertOk();
    }

    #[Test]
    public function throws_when_mismatch_from_body(): void
    {
        $this->container->get(Session::class)->set(Session::CSRF_TOKEN_KEY, 'abc');

        $this->http
            ->post('/test', [Session::CSRF_TOKEN_KEY => 'def'])
            ->assertStatus(Status::UNPROCESSABLE_CONTENT);
    }

    #[Test]
    public function throws_when_mismatch_from_header(): void
    {
        $this->container->singleton(Environment::class, Environment::PRODUCTION);
        $this->container->get(Session::class)->set(Session::CSRF_TOKEN_KEY, 'abc');

        $this->http
            ->post('/test', [Session::CSRF_TOKEN_KEY => 'def'])
            ->assertStatus(Status::UNPROCESSABLE_CONTENT);
    }

    #[Test]
    public function matches_from_body(): void
    {
        $this->container->singleton(Environment::class, Environment::PRODUCTION);

        $session = $this->container->get(Session::class);

        $this->http
            ->post('/test', [Session::CSRF_TOKEN_KEY => $session->token])
            ->assertOk();
    }

    #[Test]
    public function matches_from_header_when_encrypted(): void
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

    #[Test]
    public function csrf_component(): void
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

    #[Test]
    public function csrf_token_function(): void
    {
        $session = $this->container->get(Session::class);
        $session->set(Session::CSRF_TOKEN_KEY, 'test');

        $this->assertSame('test', csrf_token());
    }

    #[Test]
    public function csrf_with_cached_view(): void
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
