<?php

namespace Tempest\Testing\Actions;

use function Tempest\Support\str;

final class ConvertPHPUnit
{
    public function __invoke(string $input): string
    {
        $string = str($input)
            // Add uses
            ->replaceRegex('/namespace [\w\\\\]+;\s/', function (array $match) {
                return $match[0] . PHP_EOL . 'use function Tempest\Testing\test;' . PHP_EOL . 'use Tempest\Testing\Test;';
            })
            // Remove extends from PHPUnit test classes
            ->replaceRegex('/extends [\w]+/', '');

        $expectedActualPatterns = [
            'assertSame' => 'is',
            'assertInstanceOf' => 'instanceOf',
        ];

        foreach ($expectedActualPatterns as $original => $method) {
            $string = $string->replaceRegex(
                '/\$this->' . $original . '\(\s*(?<expected>.*?),\s*(?<actual>.*?)\s*\)\s*;/',
                function (array $match) use ($method) {
                    return sprintf(
                        'test(%s)->%s(%s);',
                        $match['actual'],
                        $method,
                        $match['expected'],
                    );
                },
            );
        }

        return $string->toString();
    }
}

/*
 *
 * final public static function assertArrayHasKey(mixed $key, array|ArrayAccess $array, string $message = ''): void
 * final public static function assertArrayNotHasKey(mixed $key, array|ArrayAccess $array, string $message = ''): void
 * final public static function assertIsList(mixed $array, string $message = ''): void
 * final public static function assertContains(mixed $needle, iterable $haystack, string $message = ''): void
 * final public static function assertNotContains(mixed $needle, iterable $haystack, string $message = ''): void
 * final public static function assertCount(int $expectedCount, Countable|iterable $haystack, string $message = ''): void
 * final public static function assertNotCount(int $expectedCount, Countable|iterable $haystack, string $message = ''): void
 * final public static function assertEquals(mixed $expected, mixed $actual, string $message = ''): void
 * final public static function assertNotEquals(mixed $expected, mixed $actual, string $message = ''): void
 * final public static function assertEmpty(mixed $actual, string $message = ''): void
 * final public static function assertNotEmpty(mixed $actual, string $message = ''): void
 * final public static function assertGreaterThan(mixed $minimum, mixed $actual, string $message = ''): void
 * final public static function assertGreaterThanOrEqual(mixed $minimum, mixed $actual, string $message = ''): void
 * final public static function assertLessThan(mixed $maximum, mixed $actual, string $message = ''): void
 * final public static function assertLessThanOrEqual(mixed $maximum, mixed $actual, string $message = ''): void
 * final public static function assertTrue(mixed $condition, string $message = ''): void
 * final public static function assertFalse(mixed $condition, string $message = ''): void
 * final public static function assertNull(mixed $actual, string $message = ''): void
 * final public static function assertNotNull(mixed $actual, string $message = ''): void
 * final public static function assertSame(mixed $expected, mixed $actual, string $message = ''): void
 * final public static function assertNotSame(mixed $expected, mixed $actual, string $message = ''): void
 * final public static function assertInstanceOf(string $expected, mixed $actual, string $message = ''): void
 * final public static function assertNotInstanceOf(string $expected, mixed $actual, string $message = ''): void
 * final public static function assertIsArray(mixed $actual, string $message = ''): void
 * final public static function assertIsBool(mixed $actual, string $message = ''): void
 * final public static function assertIsFloat(mixed $actual, string $message = ''): void
 * final public static function assertIsInt(mixed $actual, string $message = ''): void
 * final public static function assertIsNumeric(mixed $actual, string $message = ''): void
 * final public static function assertIsObject(mixed $actual, string $message = ''): void
 * final public static function assertIsResource(mixed $actual, string $message = ''): void
 * final public static function assertIsString(mixed $actual, string $message = ''): void
 * final public static function assertIsScalar(mixed $actual, string $message = ''): void
 * final public static function assertIsCallable(mixed $actual, string $message = ''): void
 * final public static function assertIsIterable(mixed $actual, string $message = ''): void
 * final public static function assertIsNotArray(mixed $actual, string $message = ''): void
 * final public static function assertIsNotBool(mixed $actual, string $message = ''): void
 * final public static function assertIsNotFloat(mixed $actual, string $message = ''): void
 * final public static function assertIsNotInt(mixed $actual, string $message = ''): void
 * final public static function assertIsNotNumeric(mixed $actual, string $message = ''): void
 * final public static function assertIsNotObject(mixed $actual, string $message = ''): void
 * final public static function assertIsNotResource(mixed $actual, string $message = ''): void
 * final public static function assertIsNotString(mixed $actual, string $message = ''): void
 * final public static function assertIsNotScalar(mixed $actual, string $message = ''): void
 * final public static function assertIsNotCallable(mixed $actual, string $message = ''): void
 * final public static function assertIsNotIterable(mixed $actual, string $message = ''): void
 * final public static function assertStringStartsWith(string $prefix, string $string, string $message = ''): void
 * final public static function assertStringStartsNotWith(string $prefix, string $string, string $message = ''): void
 * final public static function assertStringEndsWith(string $suffix, string $string, string $message = ''): void
 * final public static function assertStringEndsNotWith(string $suffix, string $string, string $message = ''): void
 * final public static function assertJson(string $actual, string $message = ''): void
 *
 */
