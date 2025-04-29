<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http\Responses;

use Tempest\Drift\FrameworkIntegrationTestCase;
use Tempest\Http\Responses\Ok;
use Tempest\Http\Status;

/**
 * @internal
 */
final class OkTest extends FrameworkIntegrationTestCase
{
    public function test_ok(): void
    {
        $response = new Ok('test');

        $this->assertEquals(Status::OK, $response->status);
        $this->assertEquals('test', $response->body);
    }
}
