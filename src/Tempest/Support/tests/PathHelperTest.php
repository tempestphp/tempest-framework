<?php

declare(strict_types=1);

namespace Tempest\Support\Tests;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Support\PathHelper;

/**
 * @internal
 */
final class PathHelperTest extends TestCase
{
    #[DataProvider('paths')]
    public function test_make(array $paths, string $expected): void
    {
        // Act
        $output = PathHelper::make(...$paths);

        // Assert
        $this->assertSame($expected, $output);
    }

    public static function paths(): Generator
    {
        yield 'single path' => [
            'paths' => ['/foo/'],
            'expected' => DIRECTORY_SEPARATOR . 'foo' . DIRECTORY_SEPARATOR,
        ];

        yield 'single path with forward slash' => [
            'paths' => ['/foo/bar/'],
            'expected' => DIRECTORY_SEPARATOR . 'foo' . DIRECTORY_SEPARATOR . 'bar' . DIRECTORY_SEPARATOR,
        ];

        yield 'single path with backward slash' => [
            'paths' => ['\\foo\\bar\\'],
            'expected' => DIRECTORY_SEPARATOR . 'foo' . DIRECTORY_SEPARATOR . 'bar' . DIRECTORY_SEPARATOR,
        ];

        yield 'multiple paths' => [
            'paths' => ['foo', 'bar'],
            'expected' => 'foo' . DIRECTORY_SEPARATOR . 'bar',
        ];

        yield 'multiple paths with forward slash' => [
            'paths' => ['/foo/bar/', '/baz/qux/'],
            'expected' => DIRECTORY_SEPARATOR . 'foo' . DIRECTORY_SEPARATOR . 'bar' . DIRECTORY_SEPARATOR . 'baz' . DIRECTORY_SEPARATOR . 'qux' . DIRECTORY_SEPARATOR,
        ];

        yield 'multiple paths with backward slash' => [
            'paths' => ['\\foo\\bar\\', '\\baz\\qux\\'],
            'expected' => DIRECTORY_SEPARATOR . 'foo' . DIRECTORY_SEPARATOR . 'bar' . DIRECTORY_SEPARATOR . 'baz' . DIRECTORY_SEPARATOR . 'qux' . DIRECTORY_SEPARATOR,
        ];

        yield 'single foward slash' => [
            'paths' => ['/'],
            'expected' => DIRECTORY_SEPARATOR,
        ];

        yield 'single backward slash' => [
            'paths' => ['\\'],
            'expected' => DIRECTORY_SEPARATOR,
        ];

        yield 'no slash' => [
            'paths' => ['foo'],
            'expected' => 'foo',
        ];

        yield 'starts with forward slash' => [
            'paths' => ['/foo'],
            'expected' => DIRECTORY_SEPARATOR . 'foo',
        ];

        yield 'starts with backward slash' => [
            'paths' => ['\\foo'],
            'expected' => DIRECTORY_SEPARATOR . 'foo',
        ];

        yield 'ends with forward slash' => [
            'paths' => ['foo/'],
            'expected' => 'foo' . DIRECTORY_SEPARATOR,
        ];

        yield 'ends with backward slash' => [
            'paths' => ['foo\\'],
            'expected' => 'foo' . DIRECTORY_SEPARATOR,
        ];

        yield 'first path is forward slash' => [
            'paths' => ['/', '/foo'],
            'expected' => DIRECTORY_SEPARATOR . 'foo',
        ];

        yield 'first path is backward slash' => [
            'paths' => ['\\', '\\foo'],
            'expected' => DIRECTORY_SEPARATOR . 'foo',
        ];

        yield 'last path is forward slash' => [
            'paths' => ['foo/', '/'],
            'expected' => 'foo' . DIRECTORY_SEPARATOR,
        ];

        yield 'last path is backward slash' => [
            'paths' => ['foo\\', '\\'],
            'expected' => 'foo' . DIRECTORY_SEPARATOR,
        ];
    }

    #[Test]
    #[DataProvider('toClassNameProvider')]
    public function toClassName(string $path, string $expected): void {
        $this->assertSame(
            actual: PathHelper::toClassName($path),
            expected: $expected,
        );
    }

    public static function toClassNameProvider(): array {
        return [
            'single path' => ['/Foo/Bar', 'Bar'],
            'single path end with forward slash' => ['Foo/Bar/', 'Bar'],
            'single path end with backward slash' => ['Foo/Bar\\', 'Bar'],
            'path with extension' => ['Foo/Bar.php', 'Bar'],
        ];
    }
}
