<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http\Responses;

use Tempest\Http\Responses\Redirect;
use Tempest\Http\Status;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class RedirectTest extends FrameworkIntegrationTestCase
{
    public function test_redirect(): void
    {
        $response = new Redirect('/to');

        $this->assertSame(Status::FOUND, $response->status);
        $this->assertSame('/to', $response->getHeader('Location')->values[0]);
    }

    public function test_permanent(): void
    {
        $response = new Redirect('/to')->permanent();

        $this->assertSame(Status::MOVED_PERMANENTLY, $response->status);
    }
}
