<?php

declare(strict_types=1);

namespace Tempest\AI\Tests;

use PHPUnit\Framework\TestCase;
use Tempest\AI\Exception\AIException;

final class AIExceptionTest extends TestCase
{
    public function test_can_create_exception(): void
    {
        $exception = new AIException('Test error message');

        $this->assertSame('Test error message', $exception->getMessage());
    }

    public function test_can_throw_exception(): void
    {
        $this->expectException(AIException::class);
        $this->expectExceptionMessage('Something went wrong');

        throw new AIException('Something went wrong');
    }
}
