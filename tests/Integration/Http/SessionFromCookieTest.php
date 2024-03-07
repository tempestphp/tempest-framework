<?php

namespace Tests\Tempest\Integration\Http;

use Tempest\Http\Cookie\CookieManager;
use Tempest\Http\Session\Resolvers\CookieSessionIdResolver;
use Tempest\Http\Session\Session;
use Tempest\Http\Session\SessionConfig;
use Tempest\Testing\IntegrationTest;

final class SessionFromCookieTest extends IntegrationTest
{
    /** @test */
    public function resolving_session_sets_cookie_id()
    {
        $this->container->config(new SessionConfig(
            idResolverClass: CookieSessionIdResolver::class,
        ));

        $sessionA = $this->container->get(Session::class);

        $cookieManager = $this->container->get(CookieManager::class);
        $cookieManager->set('tempest_session_id', 'b');
        $sessionA->put('test', 'a');

        $cookieManager->set('tempest_session_id', 'b');
        $sessionB = $this->container->get(Session::class);
        $this->assertNull($sessionB->get('test'));
    }
}