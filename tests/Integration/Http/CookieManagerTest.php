<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http;

use Tempest\Drift\FrameworkIntegrationTestCase;
use Tempest\Http\Cookie\Cookie;
use Tempest\Http\Cookie\CookieManager;
use Tempest\Http\Cookie\SameSite;

/**
 * @internal
 */
final class CookieManagerTest extends FrameworkIntegrationTestCase
{
    public function test_existing_cookies(): void
    {
        // @TODO(aidan-casey): Take a look at this.
        $this->markTestSkipped('Conflicts with Tests\Tempest\Integration\RouteTest::test_from_factory');

        //$_COOKIE['existing'] = 'value';
        //$cookies = $this->container->get(CookieManager::class);
        //$this->assertEquals('value', $cookies->get('existing')->value);
    }

    public function test_creating_a_cookie(): void
    {
        $cookies = $this->container->get(CookieManager::class);

        $cookies->set('new', 'value');

        $this->http
            ->get('/')
            ->assertOk()
            ->assertHeaderContains('set-cookie', 'new=value');
    }

    public function test_removing_a_cookie(): void
    {
        $cookies = $this->container->get(CookieManager::class);

        $cookies->remove('new');

        $this->http
            ->get('/')
            ->assertOk()
            ->assertHeaderContains('set-cookie', 'new=; Expires=Wed, 31-Dec-1969 23:59:59 GMT; Max-Age=0');
    }

    public function test_manually_adding_a_cookie(): void
    {
        $clock = $this->clock('2023-01-01 00:00:00');
        $cookies = $this->container->get(CookieManager::class);

        $cookies->add(new Cookie(
            key: 'key',
            value: 'value',
            expiresAt: $clock->timestamp() + 1,
            domain: 'test.com',
            path: '/test',
            secure: true,
            httpOnly: true,
            sameSite: SameSite::STRICT,
        ));

        $this->http
            ->get('/')
            ->assertOk()
            ->assertHeaderContains('set-cookie', 'key=value; Expires=Sun, 01-Jan-2023 00:00:01 GMT; Max-Age=1; Domain=test.com; Path=/test; Secure; HttpOnly; SameSite=Strict');
    }
}
