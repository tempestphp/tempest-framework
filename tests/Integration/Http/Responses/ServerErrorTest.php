<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http\Responses;

use PHPUnit\Framework\Attributes\CoversNothing;
use Tempest\Http\Status;
use Tempest\Router\Responses\ServerError;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
#[CoversNothing]
final class ServerErrorTest extends FrameworkIntegrationTestCase
{
    public function test_server_error(): void
    {
        $response = new ServerError('test');

        $this->assertSame(Status::INTERNAL_SERVER_ERROR, $response->status);
        $this->assertSame('test', $response->body);
    }
}
