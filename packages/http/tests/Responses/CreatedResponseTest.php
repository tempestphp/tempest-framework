<?php

declare(strict_types=1);

namespace Tempest\Http\Tests\Responses;

use PHPUnit\Framework\TestCase;
use Tempest\Http\Responses\Created;
use Tempest\Http\Responses\Json;
use Tempest\Http\Status;

/**
 * @internal
 */
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

    public function test_json_response(): void
    {
        $response = new Json(['foo' => 'bar']);

        $this->assertSame(Status::OK, $response->status);
        $this->assertSame(['foo' => 'bar'], $response->body);

        $accept = $response->getHeader('Accept');
        $contentType = $response->getHeader('Content-Type');

        $this->assertSame('Accept', $accept->name);
        $this->assertSame('application/json', $accept->values[0]);
        $this->assertSame('Content-Type', $contentType->name);
        $this->assertSame('application/json', $contentType->values[0]);
    }
}
