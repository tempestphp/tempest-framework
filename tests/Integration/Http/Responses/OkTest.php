<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http\Responses;

use Tempest\Http\Responses\Ok;
use Tempest\Http\Status;
use Tests\Tempest\Integration\FrameworkIntegrationTest;

/**
 * @internal
 * @small
 */
class OkTest extends FrameworkIntegrationTest
{
    public function test_ok(): void
    {
        $response = new Ok('test');

        $this->assertEquals(Status::OK, $response->getStatus());
        $this->assertEquals('test', $response->getBody());
    }
}
