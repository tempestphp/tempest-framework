<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http\Responses;

use PHPUnit\Framework\Attributes\CoversNothing;
use Tempest\Http\Status;
use Tempest\Router\Responses\Ok;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
#[CoversNothing]
final class OkTest extends FrameworkIntegrationTestCase
{
    public function test_ok(): void
    {
        $response = new Ok('test');

        $this->assertEquals(Status::OK, $response->status);
        $this->assertEquals('test', $response->body);
    }
}
