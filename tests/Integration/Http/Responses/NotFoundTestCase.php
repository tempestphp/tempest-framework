<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http\Responses;

use Tempest\Http\Responses\NotFound;
use Tempest\Http\Status;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 * @small
 */
class NotFoundTestCase extends FrameworkIntegrationTestCase
{
    public function test_not_found(): void
    {
        $response = new NotFound('test');

        $this->assertEquals(Status::NOT_FOUND, $response->getStatus());
        $this->assertEquals('test', $response->getBody());
    }
}
