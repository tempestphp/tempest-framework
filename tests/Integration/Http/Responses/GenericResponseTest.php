<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http\Responses;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Http\Cookie\Cookie;
use Tempest\Http\Cookie\CookieManager;
use Tempest\Http\Responses\Ok;
use Tempest\Http\Session\Session;
use Tempest\Http\Status;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class GenericResponseTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function sessions(): void
    {
        new Ok()->flash('success', 'Operation successful');

        $session = $this->container->get(Session::class);

        $this->assertSame('Operation successful', $session->get('success'));
    }

    #[Test]
    public function cookies(): void
    {
        $response = new Ok()->addCookie(new Cookie('test'));

        $cookieManager = $this->container->get(CookieManager::class);

        $this->assertInstanceOf(Cookie::class, $cookieManager->get('test'));

        $response->removeCookie('test');

        $this->assertSame(-1, $cookieManager->get('test')->expiresAt);
    }

    #[Test]
    public function set_status(): void
    {
        $response = new Ok()->setStatus(Status::ACCEPTED);

        $this->assertSame(Status::ACCEPTED, $response->status);
    }
}
