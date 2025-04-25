<?php

declare(strict_types=1);

namespace Tempest\Router\Tests\Responses;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tempest\Http\Status;
use Tempest\Router\Responses\Created;

/**
 * @internal
 */
#[CoversClass(Created::class)]
final class CreatedResponseTest extends TestCase
{
    public function test_created_response(): void
    {
        $response = new Created(json_encode(['foo' => 'bar']));

        $this->assertSame(Status::CREATED, $response->status);
        $this->assertSame([], $response->headers);
        $this->assertSame('{"foo":"bar"}', $response->body);
        $this->assertNotSame(Status::OK, $response->status);
    }
}
