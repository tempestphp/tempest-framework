<?php

namespace Tempest\Support\Tests;

use PHPUnit\Framework\TestCase;
use Tempest\Support\Tests\Fixtures\MemoizeClass;

final class HasMemoizationTest extends TestCase
{
    public function test_memoize(): void
    {
        $class = new MemoizeClass();

        $this->assertSame('value', $class->do());
        $this->assertSame(1, $class->counter);
        $this->assertSame('value', $class->do());
        $this->assertSame('value', $class->do());
        $this->assertSame(1, $class->counter);
    }
}
