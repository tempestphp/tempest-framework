<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http\Responses;

use Tempest\Http\Responses\ServerError;
use Tempest\Http\Status;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 * @small
 */
final class ServerErrorTest extends FrameworkIntegrationTestCase
{
    public function test_server_error(): void
    {
        $response = new ServerError('test');

        $this->assertSame(Status::INTERNAL_SERVER_ERROR, $response->getStatus());
        $this->assertSame('test', $response->getBody());
    }
}
