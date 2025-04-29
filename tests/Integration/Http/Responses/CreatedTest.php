<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http\Responses;

use Tempest\Drift\FrameworkIntegrationTestCase;
use Tempest\Http\Responses\Created;
use Tempest\Http\Status;

/**
 * @internal
 */
final class CreatedTest extends FrameworkIntegrationTestCase
{
    public function test_create(): void
    {
        $response = new Created('test');

        $this->assertEquals(Status::CREATED, $response->status);
        $this->assertEquals('test', $response->body);
    }
}
