<?php

namespace Tests\Tempest\Integration\Http;

use Tempest\Core\AppConfig;
use Tempest\DateTime\DateTime;
use Tempest\Http\Cookie\Cookie;
use Tempest\Http\Cookie\CookieManager;
use Tempest\Http\Cookie\SameSite;
use Tempest\Http\Session\Resolvers\CookieSessionIdResolver;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class CookieSessionIdResolverTest extends FrameworkIntegrationTestCase
{
    public function test_sets_cookie(): void
    {
        $cookies = $this->container->get(CookieManager::class);
        $resolver = $this->container->get(CookieSessionIdResolver::class);
        $resolver->resolve();

        $this->assertInstanceOf(Cookie::class, $cookie = $cookies->get('tempest_session_id'));
        $this->assertTrue($cookie->expiresAt->after(DateTime::now()->plusHours(2)->minusSecond()));
        $this->assertSame('/', $cookie->path);
        $this->assertTrue($cookie->secure);
        $this->assertTrue($cookie->httpOnly);
        $this->assertSame(SameSite::LAX, $cookie->sameSite);
    }

    public function test_cookie_name(): void
    {
        $this->container->get(AppConfig::class)->name = 'Tempest Cloud';

        $cookies = $this->container->get(CookieManager::class);
        $resolver = $this->container->get(CookieSessionIdResolver::class);
        $resolver->resolve();

        $this->assertInstanceOf(Cookie::class, $cookies->get('tempest_cloud_session_id'));
    }
}
