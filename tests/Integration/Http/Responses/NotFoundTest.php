<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http\Responses;

use PHPUnit\Framework\Attributes\CoversNothing;
use Tempest\Http\Status;
use Tempest\Router\Responses\NotFound;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
#[CoversNothing]
final class NotFoundTest extends FrameworkIntegrationTestCase
{
    public function test_not_found(): void
    {
        $response = new NotFound('test');

        $this->assertEquals(Status::NOT_FOUND, $response->status);
        $this->assertEquals('test', $response->body);
    }
}
