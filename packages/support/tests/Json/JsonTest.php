<?php

namespace Tempest\Support\Tests\Json;

use PHPUnit\Framework\TestCase;
use Tempest\Support\Json;
use Tempest\Support\Math;
use Tempest\Support\Str;

final class JsonTest extends TestCase
{
    public function test_decode(): void
    {
        $actual = Json\decode('{
            "name": "azjezz/psl",
            "type": "library",
            "description": "PHP Standard Library.",
            "keywords": ["php", "std", "stdlib", "utility", "psl"],
            "license": "MIT"
        }');

        $this->assertSame([
            'name' => 'azjezz/psl',
            'type' => 'library',
            'description' => 'PHP Standard Library.',
            'keywords' => ['php', 'std', 'stdlib', 'utility', 'psl'],
            'license' => 'MIT',
        ], $actual);
    }

    public function test_decode_throws_for_invalid_syntax(): void
    {
        $this->expectException(Json\Exception\JsonCouldNotBeDecoded::class);
        $this->expectExceptionMessage('The decoded property name is invalid.');

        Json\decode('{"\u0000": 1}', false);
    }

    public function test_decode_malformed_utf8(): void
    {
        $this->expectException(Json\Exception\JsonCouldNotBeDecoded::class);
        $this->expectExceptionMessage('Malformed UTF-8 characters, possibly incorrectly encoded.');

        Json\decode("\"\xC1\xBF\"");
    }

    public function test_encode(): void
    {
        $actual = Json\encode(['a']);

        $this->assertSame('["a"]', $actual);
    }

    public function test_pretty_encode(): void
    {
        $actual = Json\encode([
            'name' => 'azjezz/psl',
            'type' => 'library',
            'description' => 'PHP Standard Library.',
            'keywords' => ['php', 'std', 'stdlib', 'utility', 'psl'],
            'license' => 'MIT',
        ], true);

        $json = Str\replace(<<<JSON
        {
            "name": "azjezz/psl",
            "type": "library",
            "description": "PHP Standard Library.",
            "keywords": [
                "php",
                "std",
                "stdlib",
                "utility",
                "psl"
            ],
            "license": "MIT"
        }
        JSON, PHP_EOL, "\n");

        $this->assertSame($json, $actual);
    }

    public function test_encode_throws_for_malformed_utf8(): void
    {
        $this->expectException(Json\Exception\JsonCouldNotBeEncoded::class);
        $this->expectExceptionMessage('Malformed UTF-8 characters, possibly incorrectly encoded.');

        Json\encode(["bad utf\xFF"]);
    }

    public function test_encode_throws_with_nan(): void
    {
        $this->expectException(Json\Exception\JsonCouldNotBeEncoded::class);
        $this->expectExceptionMessage('Inf and NaN cannot be JSON encoded.');

        Json\encode(Math\NAN);
    }

    public function test_encode_throws_with_inf(): void
    {
        $this->expectException(Json\Exception\JsonCouldNotBeEncoded::class);
        $this->expectExceptionMessage('Inf and NaN cannot be JSON encoded.');

        Json\encode(Math\INFINITY);
    }

    public function test_encode_preserve_zero_fraction(): void
    {
        $this->assertSame('1.0', Json\encode(1.0));
    }

    public function test_is_valid(): void
    {
        $this->assertTrue(Json\is_valid('{"foo": "bar"}'));
        $this->assertTrue(Json\is_valid('"foo"'));

        $this->assertFalse(Json\is_valid('invalid'));
        $this->assertFalse(Json\is_valid('{"foo": "bar",}'));
        $this->assertFalse(Json\is_valid('{"foo": "bar", "baz": }'));

        $this->assertFalse(Json\is_valid(['foo' => 'bar']));
        $this->assertFalse(Json\is_valid(1));
    }

    public function test_base64_encode_and_decode(): void
    {
        $data = [
            'name' => 'azjezz/psl',
            'type' => 'library',
            'description' => 'PHP Standard Library.',
            'keywords' => ['php', 'std', 'stdlib', 'utility', 'psl'],
            'license' => 'MIT',
        ];

        $encoded = Json\encode($data, base64: true);
        $decoded = Json\decode($encoded, base64: true);

        $this->assertSame($data, $decoded);
    }

    public function test_base64_decode_failure(): void
    {
        $this->expectException(Json\Exception\JsonCouldNotBeDecoded::class);
        $this->expectExceptionMessage('The provided base64 string is not valid.');

        Json\decode('invalid_base64', base64: true);
    }
}
