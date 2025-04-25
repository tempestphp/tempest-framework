<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http\Responses;

use PHPUnit\Framework\Attributes\CoversNothing;
use Tempest\Http\Status;
use Tempest\Router\Responses\Created;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
#[CoversNothing]
final class CreatedTest extends FrameworkIntegrationTestCase
{
    public function test_create(): void
    {
        $response = new Created('test');

        $this->assertEquals(Status::CREATED, $response->status);
        $this->assertEquals('test', $response->body);
    }
}
