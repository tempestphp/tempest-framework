<?php

namespace Tempest\Support\Tests\Path;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Tempest\Support\Str\ImmutableString;

use function Tempest\Support\Arr\wrap;
use function Tempest\Support\Path\is_absolute_path;
use function Tempest\Support\Path\is_relative_path;
use function Tempest\Support\Path\normalize;
use function Tempest\Support\Path\to_absolute_path;
use function Tempest\Support\Path\to_relative_path;

final class FunctionsTest extends TestCase
{
    #[TestWith(['/invalid/file', true])]
    #[TestWith(['C:/file.txt', true])]
    #[TestWith([null, false])]
    #[TestWith(['', false])]
    #[TestWith(['foo', false])]
    #[TestWith(['foo/bar', false])]
    public function test_is_absolute_path(?string $path, bool $expected): void
    {
        $this->assertSame($expected, is_absolute_path($path));
    }

    public function test_is_absolute_path_with_different_types(): void
    {
        $this->assertTrue(is_absolute_path('/foo', null, '', new ImmutableString('bar')));
    }

    public function test_is_absolute_path_with_actual_file(): void
    {
        $this->assertTrue(is_absolute_path(__FILE__));
        $this->assertFalse(is_absolute_path(basename(__FILE__)));
    }

    #[TestWith(['/invalid/file', false])]
    #[TestWith(['C:/file.txt', false])]
    #[TestWith([null, true])]
    #[TestWith(['', true])]
    #[TestWith(['foo', true])]
    #[TestWith(['foo/bar', true])]
    public function test_is_relative_path(?string $path, bool $expected): void
    {
        $this->assertSame($expected, is_relative_path($path));
    }

    public function test_is_relative_path_with_actual_file(): void
    {
        $this->assertFalse(is_relative_path(__FILE__));
        $this->assertTrue(is_relative_path(basename(__FILE__)));
    }

    public function test_is_relative_path_with_different_types(): void
    {
        $this->assertFalse(is_relative_path('/foo', null, '', new ImmutableString('bar')));
    }

    #[TestWith(['/', '/some/file', 'some/file'])]
    #[TestWith(['/some', '/some/file', 'file'])]
    #[TestWith(['/some/file', '/some/file', '.'])]
    #[TestWith(['/some/file', '/some/file.txt', '../file.txt'])]
    #[TestWith(['/some/file', '/some/file/file.txt', 'file.txt'])]
    #[TestWith(['/some/file', 'file.txt', 'file.txt'])]
    #[TestWith(['/some/foo', 'bar/file.txt', 'bar/file.txt'])]
    public function test_to_relative_path(string $from, ?string $path, string $expected): void
    {
        $this->assertSame($expected, to_relative_path($from, $path));
    }

    #[TestWith(['/', '/', '/'])]
    #[TestWith(['/', '/foo', '/foo'])]
    #[TestWith(['/', '/foo/bar', '/foo/bar'])]
    #[TestWith(['/', '/foo/bar', '/foo/bar'])]
    #[TestWith(['/foo', '/foo/bar', '/foo/bar'])]
    #[TestWith(['/foo', 'foo/bar', '/foo/foo/bar'])]
    #[TestWith(['C:/', 'foo/bar', 'C:/foo/bar'])]
    #[TestWith(['C:/foo', 'foo/bar', 'C:/foo/foo/bar'])]
    #[TestWith(['C:/foo', '../foo/bar', 'C:/foo/bar'])]
    #[TestWith(['C:/foo', '../foo', 'C:/foo'])]
    #[TestWith(['C:/foo', 'bar.txt', 'C:/foo/bar.txt'])]
    #[TestWith(['C:/foo', './bar.txt', 'C:/foo/bar.txt'])]
    #[TestWith(['C:/foo', '../bar.txt', 'C:/bar.txt'])]
    #[TestWith(['C:/foo', '../baz/bar.txt', 'C:/baz/bar.txt'])]
    #[TestWith(['C:/foo', 'C:/foo/bar/baz.txt', 'C:/foo/bar/baz.txt'])]
    #[TestWith(['/foo/bar', '/foo/bar', '/foo/bar'])]
    #[TestWith(['/foo/bar', ['/foo/bar', '/baz'], '/foo/bar/baz'])]
    #[TestWith(['/foo/bar', ['/foo/bar', '/foo/bar'], '/foo/bar/foo/bar'])]
    #[TestWith(['/other/root', '/foo/bar', '/other/root/foo/bar'])]
    public function test_to_absolute_path(string $cwd, null|array|string $path, string $expected): void
    {
        $this->assertSame($expected, to_absolute_path($cwd, ...wrap($path)));
    }

    #[DataProvider('paths')]
    public function test_normalize(array $paths, string $expected): void
    {
        $this->assertSame($expected, normalize(...$paths));
    }

    public static function paths(): Generator
    {
        yield 'single path' => [
            'paths' => ['/foo/'],
            'expected' => '/foo/',
        ];

        yield 'single path with forward slash' => [
            'paths' => ['/foo/bar/'],
            'expected' => '/foo/bar/',
        ];

        yield 'single path with backward slash' => [
            'paths' => ['\\foo\\bar\\'],
            'expected' => '/foo/bar/',
        ];

        yield 'multiple paths' => [
            'paths' => ['foo', 'bar'],
            'expected' => 'foo/bar',
        ];

        yield 'multiple paths with forward slash' => [
            'paths' => ['/foo/bar/', '/baz/qux/'],
            'expected' => '/foo/bar/baz/qux/',
        ];

        yield 'multiple paths with backward slash' => [
            'paths' => ['\\foo\\bar\\', '\\baz\\qux\\'],
            'expected' => '/foo/bar/baz/qux/',
        ];

        yield 'single forward slash' => [
            'paths' => ['/'],
            'expected' => '/',
        ];

        yield 'single backward slash' => [
            'paths' => ['\\'],
            'expected' => '/',
        ];

        yield 'no slash' => [
            'paths' => ['foo'],
            'expected' => 'foo',
        ];

        yield 'starts with forward slash' => [
            'paths' => ['/foo'],
            'expected' => '/foo',
        ];

        yield 'starts with backward slash' => [
            'paths' => ['\\foo'],
            'expected' => '/foo',
        ];

        yield 'ends with forward slash' => [
            'paths' => ['foo/'],
            'expected' => 'foo/',
        ];

        yield 'ends with backward slash' => [
            'paths' => ['foo\\'],
            'expected' => 'foo/',
        ];

        yield 'first path is forward slash' => [
            'paths' => ['/', '/foo'],
            'expected' => '/foo',
        ];

        yield 'first path is backward slash' => [
            'paths' => ['\\', '\\foo'],
            'expected' => '/foo',
        ];

        yield 'last path is forward slash' => [
            'paths' => ['foo/', '/'],
            'expected' => 'foo/',
        ];

        yield 'last path is backward slash' => [
            'paths' => ['foo\\', '\\'],
            'expected' => 'foo/',
        ];
    }
}
