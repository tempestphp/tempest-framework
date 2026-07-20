<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http\Responses;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Http\Responses\Ok;
use Tempest\Http\Status;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class OkTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function ok(): void
    {
        $response = new Ok('test');

        $this->assertEquals(Status::OK, $response->status);
        $this->assertEquals('test', $response->body);
    }
}
