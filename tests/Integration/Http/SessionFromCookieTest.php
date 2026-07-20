<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http;

use PHPUnit\Framework\Attributes\Test;
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
    #[Test]
    public function resolving_session_from_cookie(): void
    {
        $this->container->config(new FileSessionConfig(
            expiration: Duration::hours(2),
            path: 'test_sessions',
        ));

        $cookieManager = $this->container->get(CookieManager::class);

        $cookieManager->set('tempest_session_id', 'session_a');

        $sessionA = $this->container->get(Session::class);
        $sessionA->set('test', 'a');

        $sessionA = $this->container->get(Session::class);
        $this->assertEquals('a', $sessionA->get('test'));
    }

    #[Test]
    public function cookie_expiration(): void
    {
        $clock = $this->clock('2023-01-01 00:00:00');

        $this->container->config(new FileSessionConfig(
            expiration: Duration::second(),
            path: 'test_sessions',
        ));

        // Resolve the session so that the ID is set
        $this->container->get(Session::class);

        $cookieManager = $this->container->get(CookieManager::class);
        $cookie = $cookieManager->get('tempest_session_id');

        $this->assertEquals(1, $cookie->maxAge);
        $this->assertEquals($clock->seconds() + 1, $cookie->getExpiresAtTime());
    }
}
