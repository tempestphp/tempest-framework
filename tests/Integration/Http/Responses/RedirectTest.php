<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http\Responses;

use Tempest\Http\Status;
use Tempest\Router\Responses\Redirect;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 * @small
 */
final class RedirectTest extends FrameworkIntegrationTestCase
{
    public function test_redirect(): void
    {
        $response = new Redirect('/to');

        $this->assertSame(Status::FOUND, $response->getStatus());
        $this->assertSame('/to', $response->getHeader('Location')->values[0]);
    }

    public function test_permanent(): void
    {
        $response = (new Redirect('/to'))->permanent();

        $this->assertSame(Status::MOVED_PERMANENTLY, $response->getStatus());
    }
}
