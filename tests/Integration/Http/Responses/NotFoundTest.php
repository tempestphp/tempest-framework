<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http\Responses;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Http\Responses\NotFound;
use Tempest\Http\Status;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class NotFoundTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function not_found(): void
    {
        $response = new NotFound('test');

        $this->assertEquals(Status::NOT_FOUND, $response->status);
        $this->assertEquals('test', $response->body);
    }
}
