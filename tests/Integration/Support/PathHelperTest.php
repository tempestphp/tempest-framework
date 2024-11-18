<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Support;

use Exception;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Support\PathHelper;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use function Tempest\path;

/**
 * @internal
 */
final class PathHelperTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function path_to_registered_namespace(): void
    {
        ld(path('src/Tempest/Auth/src/SomeNewClass.php')->toMainNamespace());
        $this->assertSame('Tempest\\Auth', path('src/Tempest/Auth/src/SomeNewClass.php')->toMainNamespace());
        $this->assertSame('Tempest\\Auth\\SomeDirectory', path('src/Tempest/Auth/src/SomeDirectory')->toMainNamespace());
        $this->assertSame('Tempest\\Auth', path($this->root . '/src/Tempest/Auth/src/SomeNewClass.php')->toMainNamespace());
        $this->assertSame('Tempest\\Auth\\SomeDirectory', path($this->root . '/src/Tempest/Auth/src/SomeDirectory')->toMainNamespace());
    }

    #[Test]
    public function paths_to_non_registered_namespace_throw(): void
    {
        $this->expectException(Exception::class);
        PathHelper::toRegisteredNamespace('app/SomeNewClass.php');
    }

    #[Test]
    public function path_to_namespace(): void
    {
        $this->assertSame('App', path('app/SomeNewClass.php')->toNamespace());
        $this->assertSame('App\\Foo\\Bar', path('app/Foo/Bar/SomeNewClass.php')->toNamespace());
        $this->assertSame('App\\Foo\\Bar\\Baz', path('app/Foo/Bar/Baz')->toNamespace());
        $this->assertSame('App\\FooBar', path('app\\FooBar\\')->toNamespace());
        $this->assertSame('App\\FooBar', path('app\\FooBar\\File.php')->toNamespace());

        $this->assertSame('App\\Foo', path('/home/project-name/app/Foo/Bar.php')->toNamespace(root: '/home/project-name'));
        $this->assertSame('App\\Foo', path('/home/project-name/app/Foo/Bar.php')->toNamespace(root: '/home/project-name/'));

        // we don't support skill issues
        $this->assertSame('Home\ProjectName\App\Foo', path('/home/project-name/app/Foo/Bar.php')->toNamespace());
    }
}
