<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http\Responses;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Http\Responses\Created;
use Tempest\Http\Status;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class CreatedTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function create(): void
    {
        $response = new Created('test');

        $this->assertEquals(Status::CREATED, $response->status);
        $this->assertEquals('test', $response->body);
    }
}
