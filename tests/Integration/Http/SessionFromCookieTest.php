<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http;

use Tempest\DateTime\Duration;
use Tempest\Http\Cookie\CookieManager;
use Tempest\Http\Session\Config\FileSessionConfig;
use Tempest\Http\Session\Session;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class SessionFromCookieTest extends FrameworkIntegrationTestCase
{
    public function test_resolving_session_from_cookie(): void
    {
        $this->container->config(new FileSessionConfig(
            path: 'test_sessions',
            expiration: Duration::hours(2),
        ));

        $cookieManager = $this->container->get(CookieManager::class);

        $cookieManager->set('tempest_session_id', 'session_a');

        $sessionA = $this->container->get(Session::class);
        $sessionA->set('test', 'a');

        $sessionA = $this->container->get(Session::class);
        $this->assertEquals('a', $sessionA->get('test'));
    }

    public function test_cookie_expiration(): void
    {
        $clock = $this->clock('2023-01-01 00:00:00');

        $this->container->config(new FileSessionConfig(
            path: 'test_sessions',
            expiration: Duration::second(),
        ));

        // Resolve the session so that the ID is set
        $this->container->get(Session::class);

        $cookieManager = $this->container->get(CookieManager::class);
        $cookie = $cookieManager->get('tempest_session_id');

        $this->assertEquals(1, $cookie->maxAge);
        $this->assertEquals($clock->seconds() + 1, $cookie->getExpiresAtTime());
    }
}
