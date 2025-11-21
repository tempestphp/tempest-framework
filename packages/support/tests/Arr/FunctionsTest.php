<?php

namespace Tempest\Support\Tests\Arr;

use Tempest\Support\Arr;
use Tempest\Testing\Test;
use function Tempest\Testing\test;

final class FunctionsTest
{
    #[Test]
    public function test_forget_values_mutates_array(): void
    {
        $original = [
            'foo',
            'bar',
        ];

        Arr\forget_values($original, ['foo']);

        test($original)->hasCount(1)->contains('bar');
    }

    #[Test]
    public function test_remove_values_does_not_mutate_array(): void
    {
        $original = [
            'foo',
            'bar',
        ];

        $result = Arr\remove_values($original, ['foo']);

        test($original)
            ->hasCount(2)
            ->contains('foo')
            ->contains('bar');

        test($result)
            ->hasCount(1)
            ->contains('bar');
    }

    #[Test]
    public function test_forget_keys_mutates_array(): void
    {
        $original = [
            'foo' => 'bar',
            'baz' => 'qux',
        ];

        Arr\forget_keys($original, ['foo']);

        test($original)
            ->hasCount(1)
            ->hasKey('baz')
            ->hasNoKey('foo');
    }

    #[Test]
    public function test_remove_keys_does_not_mutate_array(): void
    {
        $original = [
            'foo' => 'bar',
            'baz' => 'qux',
        ];

        $result = Arr\remove_keys($original, ['foo']);

        test($original)
            ->hasCount(2)
            ->hasKey('foo')
            ->hasKey('baz');

        test($result)
            ->hasCount(1)
            ->hasNoKey('foo')
            ->hasKey('baz');
    }
}
