<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http\Responses;

use Tempest\Http\Responses\NotFound;
use Tempest\Http\Status;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class NotFoundTest extends FrameworkIntegrationTestCase
{
    public function test_not_found(): void
    {
        $response = new NotFound('test');

        $this->assertEquals(Status::NOT_FOUND, $response->status);
        $this->assertEquals('test', $response->body);
    }
}
