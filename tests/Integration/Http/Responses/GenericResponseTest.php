<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http\Responses;

use Tempest\Http\Status;
use Tempest\Router\Cookie\Cookie;
use Tempest\Router\Cookie\CookieManager;
use Tempest\Router\Responses\Ok;
use Tempest\Router\Session\Session;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 * @small
 */
final class GenericResponseTest extends FrameworkIntegrationTestCase
{
    public function test_sessions(): void
    {
        $response = (new Ok())
            ->addSession('test', 'test')
            ->addSession('original', 'original');

        $session = $this->container->get(Session::class);

        $this->assertSame('test', $session->get('test'));

        $response->removeSession('test');

        $this->assertNull($session->get('test'));

        $response->destroySession();

        $this->assertNull($session->get('original'));
    }

    public function test_cookies(): void
    {
        $response = (new Ok())->addCookie(new Cookie('test'));

        $cookieManager = $this->container->get(CookieManager::class);

        $this->assertInstanceOf(Cookie::class, $cookieManager->get('test'));

        $response->removeCookie('test');

        $this->assertSame(-1, $cookieManager->get('test')->expiresAt);
    }

    public function test_set_status(): void
    {
        $response = (new Ok())->setStatus(Status::ACCEPTED);

        $this->assertSame(Status::ACCEPTED, $response->getStatus());
    }
}
