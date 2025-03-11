<?php

declare(strict_types=1);

namespace Tempest\Support\Tests\Random;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Tempest\Support\Random;

use function Tempest\Support\Str\contains;

/**
 * @internal
 */
final class FunctionsTest extends TestCase
{
    public function test_string(): void
    {
        $random = Random\secure_string(32);

        $this->assertSame(32, mb_strlen($random));

        foreach (mb_str_split($random) as $char) {
            $this->assertTrue(contains('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', $char));
        }
    }

    public function test_string_with_specific_chars(): void
    {
        $random = Random\secure_string(32, 'abc');

        $this->assertSame(32, mb_strlen($random));

        foreach (mb_str_split($random) as $char) {
            $this->assertTrue(contains('abc', $char));
        }
    }

    public function test_string_early_return_for_zero_length(): void
    {
        $this->assertSame('', Random\secure_string(0));
    }

    public function test_string_alphabet_min(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('$alphabet\'s length must be in [2^1, 2^56]');

        Random\secure_string(32, 'a');
    }
}
