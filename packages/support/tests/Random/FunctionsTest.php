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

    public function test_uuid(): void
    {
        $this->assertTrue(Random\is_uuid(Random\uuid()));
    }

    public function test_ulid(): void
    {
        $this->assertTrue(Random\is_ulid(Random\ulid()));
    }

    public function test_is_uuid(): void
    {
        $this->assertTrue(Random\is_uuid(Random\uuid()));

        // UUID v1
        $this->assertTrue(Random\is_uuid('CB2F46B4-D0C6-11EE-A506-0242AC120002'));
        $this->assertTrue(Random\is_uuid('cb2f46b4-d0c6-11ee-a506-0242ac120002'));

        // UUID v4
        $this->assertTrue(Random\is_uuid('0EC29141-3D58-4187-B664-2D93B7DA0D31'));
        $this->assertTrue(Random\is_uuid('0ec29141-3d58-4187-b664-2d93b7da0d31'));

        // UUID v7
        $this->assertTrue(Random\is_uuid('018DCC19-7E65-7C4B-9B14-9A11DF3E0FDB'));
        $this->assertTrue(Random\is_uuid('018dcc19-7e65-7c4b-9b14-9a11df3e0fdb'));

        $this->assertFalse(Random\is_uuid(''));
        $this->assertFalse(Random\is_uuid('01JVX9G569ETXTZKKCK94T4A6V'));
        $this->assertFalse(Random\is_uuid('foo'));
        $this->assertFalse(Random\is_uuid(Random\secure_string(26)));
        $this->assertFalse(Random\is_uuid(Random\secure_string(36)));
        $this->assertFalse(Random\is_uuid(null));
    }

    public function test_is_ulid(): void
    {
        $this->assertTrue(Random\is_ulid(Random\ulid()));

        $this->assertTrue(Random\is_ulid('01JVX9G569ETXTZKKCK94T4A6V'));

        $this->assertFalse(Random\is_ulid(''));
        $this->assertFalse(Random\is_ulid('0ec29141-3d58-4187-b664-2d93b7da0d31'));
        $this->assertFalse(Random\is_ulid('018dcc19-7e65-7c4b-9b14-9a11df3e0fdb'));
        $this->assertFalse(Random\is_ulid('foo'));
        $this->assertFalse(Random\is_ulid(null));
    }
}
