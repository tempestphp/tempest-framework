<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http;

use Tempest\Core\AppConfig;
use Tempest\Http\Cookie\Cookie;
use Tempest\Http\Cookie\CookieManager;
use Tempest\Http\Cookie\SameSite;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class CookieManagerTest extends FrameworkIntegrationTestCase
{
    public function test_cookie_manager_does_not_get_initialized_with_request_cookies(): void
    {
        $_COOKIE['existing'] = 'value';

        $cookies = $this->container->get(CookieManager::class);

        $this->assertNull($cookies->get('existing'));

        unset($_COOKIE['existing']);
    }

    public function test_creating_a_cookie(): void
    {
        $this->container->get(AppConfig::class)->baseUri = 'https://test.com';

        $cookies = $this->container->get(CookieManager::class);
        $cookies->set('new', 'value');

        $this->http
            ->get('/')
            ->assertOk()
            ->assertHeaderMatches('set-cookie', 'new=%s; Path=/; Secure; HttpOnly; SameSite=Lax')
            ->assertHasCookie('new', 'value');
    }

    public function test_creating_a_cookie_with_unsecure_local_host(): void
    {
        $this->container->get(AppConfig::class)->baseUri = 'http://test.com';

        $cookies = $this->container->get(CookieManager::class);
        $cookies->set('new', 'value');

        $this->http
            ->get('/')
            ->assertOk()
            ->assertHeaderMatches('set-cookie', 'new=%s; Path=/; HttpOnly; SameSite=Lax')
            ->assertHasCookie('new', 'value');
    }

    public function test_removing_a_cookie(): void
    {
        $cookies = $this->container->get(CookieManager::class);
        $cookies->remove('new');

        $this->http
            ->get('/')
            ->assertOk()
            ->assertHeaderContains('set-cookie', 'new=; Expires=Wed, 31-Dec-1969 23:59:59 GMT; Max-Age=0; Path=/; Secure; SameSite=Lax');
    }

    public function test_manually_adding_a_cookie(): void
    {
        $clock = $this->clock('2023-01-01 00:00:00');
        $cookies = $this->container->get(CookieManager::class);

        $cookies->add(new Cookie(
            key: 'key',
            value: 'value',
            expiresAt: $clock->seconds() + 1,
            domain: 'test.com',
            path: '/test',
            secure: true,
            httpOnly: true,
            sameSite: SameSite::STRICT,
        ));

        $this->http
            ->get('/')
            ->assertOk()
            ->assertHeaderMatches('set-cookie', 'key=%s; Expires=Sun, 01-Jan-2023 00:00:01 GMT; Max-Age=1; Domain=test.com; Path=/test; Secure; HttpOnly; SameSite=Strict');
    }
}
