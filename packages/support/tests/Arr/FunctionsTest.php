<?php

namespace Tempest\Support\Tests\Arr;

use PHPUnit\Framework\TestCase;
use Tempest\Support\Arr;

final class FunctionsTest extends TestCase
{
    public function test_forget_values_mutates_array(): void
    {
        $original = [
            'foo',
            'bar',
        ];

        Arr\forget_values($original, ['foo']);

        $this->assertCount(1, $original);
        $this->assertContains('bar', $original);
    }

    public function test_remove_values_does_not_mutate_array(): void
    {
        $original = [
            'foo',
            'bar',
        ];

        $result = Arr\remove_values($original, ['foo']);

        $this->assertCount(2, $original);
        $this->assertContains('foo', $original);
        $this->assertContains('bar', $original);

        $this->assertCount(1, $result);
        $this->assertContains('bar', $result);
    }

    public function test_forget_keys_mutates_array(): void
    {
        $original = [
            'foo' => 'bar',
            'baz' => 'qux',
        ];

        Arr\forget_keys($original, ['foo']);

        $this->assertCount(1, $original);
        $this->assertArrayNotHasKey('foo', $original);
        $this->assertArrayHasKey('baz', $original);
    }

    public function test_remove_keys_does_not_mutate_array(): void
    {
        $original = [
            'foo' => 'bar',
            'baz' => 'qux',
        ];

        $result = Arr\remove_keys($original, ['foo']);

        $this->assertCount(2, $original);
        $this->assertArrayHasKey('foo', $original);
        $this->assertArrayHasKey('baz', $original);

        $this->assertCount(1, $result);
        $this->assertArrayNotHasKey('foo', $result);
        $this->assertArrayHasKey('baz', $result);
    }
}
