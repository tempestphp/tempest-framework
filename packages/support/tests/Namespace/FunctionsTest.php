<?php

namespace Tempest\Support\Tests\Namespace;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Tempest\Support\Namespace\PathCouldNotBeMappedToNamespace;
use Tempest\Support\Namespace\Psr4Namespace;

use function Tempest\Support\Namespace\to_base_class_name;
use function Tempest\Support\Namespace\to_namespace;
use function Tempest\Support\Namespace\to_psr4_namespace;

final class FunctionsTest extends TestCase
{
    #[TestWith(['app/SomeNewClass.php', null, 'App'])]
    #[TestWith(['app/Foo/Bar/SomeNewClass.php', null, 'App\\Foo\\Bar'])]
    #[TestWith(['app/Foo/Bar/Baz', null, 'App\\Foo\\Bar\\Baz'])]
    #[TestWith(['app\\FooBar\\', null, 'App\\FooBar'])]
    #[TestWith(['app\\FooBar\\File.php', null, 'App\\FooBar'])]
    #[TestWith(['/home/project-name/app/Foo/Bar.php', '/home/project-name', 'App\\Foo'])]
    #[TestWith(['/home/project-name/app/Foo/Bar.php', '/home/project-name/', 'App\\Foo'])]
    #[TestWith(['/home/project-name/app/Foo/Bar.php', null, 'Home\ProjectName\App\Foo'])] // we don't support skill issues
    public function test_to_namespace(string $path, ?string $root, string $expected): void
    {
        $this->assertSame($expected, to_namespace($path, $root));
    }

    #[TestWith(['src/Tempest/Auth/src/SomeNewClass.php', 'Tempest\\Auth'])]
    #[TestWith(['src/Tempest/Auth/src/SomeDirectory', 'Tempest\\Auth\\SomeDirectory'])]
    #[TestWith(['/foo/bar/src/Tempest/Auth/src/SomeDirectory', 'Tempest\\Auth\\SomeDirectory', '/foo/bar'])]
    public function test_to_composer_namespace(string $path, string $expected, ?string $root = null): void
    {
        $namespace = new Psr4Namespace('Tempest\\Auth\\', './src/Tempest/Auth/src');

        $this->assertSame($expected, to_psr4_namespace([$namespace], $path, $root));
    }

    #[TestWith(['src/Tempest/Auth/src/SomeNewClass.php', 'Tempest\\Auth'])]
    #[TestWith(['src/Tempest/Auth/src/SomeDirectory', 'Tempest\\Auth\\SomeDirectory'])]
    #[TestWith(['/foo/bar/src/Tempest/Auth/src/SomeDirectory', 'Tempest\\Auth\\SomeDirectory', '/foo/bar'])]
    public function test_to_composer_namespace_without_leading_slashed(string $path, string $expected, ?string $root = null): void
    {
        $namespace = new Psr4Namespace('Tempest\\Auth\\', 'src/Tempest/Auth/src');

        $this->assertSame($expected, to_psr4_namespace([$namespace], $path, $root));
    }

    #[TestWith(['src/Tempest/OtherNamespace/src/SomeDirectory', 'Tempest\\Auth\\Src\\Tempest\\OtherNamespace\\Src\\SomeDirectory'])]
    #[TestWith(['Foo', 'Tempest\\Auth\\Foo'])]
    #[TestWith(['Foo/Bar.php', 'Tempest\\Auth\\Foo'])]
    #[TestWith(['/foo/baz/Foo/Bar.php', 'Tempest\\Auth\\Foo', '/foo/bar'])]
    public function test_to_composer_namespace_exceptions(string $path, string $expected, ?string $root = null): void
    {
        $this->expectException(PathCouldNotBeMappedToNamespace::class);

        $namespace = new Psr4Namespace('Tempest\\Auth\\', 'src/Tempest/Auth/src');

        $this->assertSame($expected, to_psr4_namespace([$namespace], $path, $root));
    }

    #[TestWith(['/Foo/Bar', 'Bar'])]
    #[TestWith(['Foo/Bar/', 'Bar'])]
    #[TestWith(['Foo/Bar\\', 'Bar'])]
    #[TestWith(['Foo/Bar.php', 'Bar'])]
    #[TestWith(['src/Tempest/Vite/Vite.php', 'Vite'])]
    #[TestWith(['spp/Vite.php', 'Vite'])]
    #[TestWith(['Vite.php', 'Vite'])]
    #[TestWith(['Vite', 'Vite'])]
    #[TestWith([\Tempest\Vite\Vite::class, 'Vite'])]
    public function test_to_base_class_name(string $path, string $expected): void
    {
        $this->assertSame($expected, to_base_class_name($path));
    }
}
