<?php

declare(strict_types=1);

namespace Tempest\Http\Tests\Responses;

use PHPUnit\Framework\TestCase;
use Tempest\Http\Responses\Created;
use Tempest\Http\Status;

/**
 * @internal
 * @small
 */
final class CreatedResponseTest extends TestCase
{
    public function test_created_response(): void
    {
        $response = new Created(json_encode(['foo' => 'bar']));

        $this->assertSame(Status::CREATED, $response->getStatus());
        $this->assertSame([], $response->getHeaders());
        $this->assertSame('{"foo":"bar"}', $response->getBody());
        $this->assertNotSame(Status::OK, $response->getStatus());
    }
}
