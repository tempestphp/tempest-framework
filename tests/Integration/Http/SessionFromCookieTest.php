<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http;

use Tempest\Router\Cookie\CookieManager;
use Tempest\Router\Session\Resolvers\CookieSessionIdResolver;
use Tempest\Router\Session\Session;
use Tempest\Router\Session\SessionConfig;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 * @small
 */
final class SessionFromCookieTest extends FrameworkIntegrationTestCase
{
    public function test_resolving_session_from_cookie(): void
    {
        $this->container->config(new SessionConfig(
            path: __DIR__ . '/sessions',
            idResolverClass: CookieSessionIdResolver::class,
        ));

        $cookieManager = $this->container->get(CookieManager::class);

        $cookieManager->set('tempest_session_id', 'session_a');

        $sessionA = $this->container->get(Session::class);
        $sessionA->set('test', 'a');

        $cookieManager->set('tempest_session_id', 'session_b');
        $sessionB = $this->container->get(Session::class);
        $this->assertNull($sessionB->get('test'));

        $cookieManager->set('tempest_session_id', 'session_a');
        $sessionA = $this->container->get(Session::class);
        $this->assertEquals('a', $sessionA->get('test'));
    }

    public function test_cookie_expiration(): void
    {
        $clock = $this->clock('2023-01-01 00:00:00');

        $this->container->config(new SessionConfig(
            path: __DIR__ . '/sessions',
            expirationInSeconds: 1,
        ));

        // Resolve the session so that the ID is set
        $this->container->get(Session::class);

        $cookieManager = $this->container->get(CookieManager::class);
        $cookie = $cookieManager->get(Session::ID);

        $this->assertEquals(1, $cookie->maxAge);
        $this->assertEquals($clock->time() + 1, $cookie->getExpiresAtTime());
    }
}
