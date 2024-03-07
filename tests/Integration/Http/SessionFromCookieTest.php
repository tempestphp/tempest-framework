<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http;

use Tempest\Http\Cookie\CookieManager;
use Tempest\Http\Session\Resolvers\CookieSessionIdResolver;
use Tempest\Http\Session\Session;
use Tempest\Http\Session\SessionConfig;
use Tempest\Testing\IntegrationTest;

/**
 * @internal
 * @small
 */
final class SessionFromCookieTest extends IntegrationTest
{
    public function test_resolving_session_sets_cookie_id()
    {
        $this->container->config(new SessionConfig(
            path: __DIR__ . '/sessions',
            idResolverClass: CookieSessionIdResolver::class,
        ));

        $cookieManager = $this->container->get(CookieManager::class);

        $cookieManager->set('tempest_session_id', 'session_a');
        $sessionA = $this->container->get(Session::class);
        $sessionA->put('test', 'a');

        $cookieManager->set('tempest_session_id', 'session_b');
        $sessionB = $this->container->get(Session::class);
        $this->assertNull($sessionB->get('test'));

        $cookieManager->set('tempest_session_id', 'session_a');
        $sessionA = $this->container->get(Session::class);
        $this->assertEquals('a', $sessionA->get('test'));
    }
}
