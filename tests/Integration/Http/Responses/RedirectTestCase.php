<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http\Responses;

use Tempest\Http\Responses\Redirect;
use Tempest\Http\Status;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 * @small
 */
class RedirectTestCase extends FrameworkIntegrationTestCase
{
    public function test_invalid(): void
    {
        $response = new Redirect('/to');

        $this->assertSame(Status::FOUND, $response->getStatus());
        $this->assertSame('/to', $response->getHeader('Location')->values[0]);
    }
}
