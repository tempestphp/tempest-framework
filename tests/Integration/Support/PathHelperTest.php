<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Support;

use Exception;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Support\PathHelper;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class PathHelperTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function path_to_registered_namespace(): void
    {
        $this->assertSame('Tempest\\Auth', PathHelper::toRegisteredNamespace('src/Tempest/Auth/src/SomeNewClass.php'));
        $this->assertSame('Tempest\\Auth\\SomeDirectory', PathHelper::toRegisteredNamespace('src/Tempest/Auth/src/SomeDirectory'));
        $this->assertSame('Tempest\\Auth', PathHelper::toRegisteredNamespace($this->root.'/src/Tempest/Auth/src/SomeNewClass.php'));
        $this->assertSame('Tempest\\Auth\\SomeDirectory', PathHelper::toRegisteredNamespace($this->root.'/src/Tempest/Auth/src/SomeDirectory'));
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
        $this->assertSame('App', PathHelper::toNamespace('app/SomeNewClass.php'));
        $this->assertSame('App\\Foo\\Bar', PathHelper::toNamespace('app/Foo/Bar/SomeNewClass.php'));
        $this->assertSame('App\\Foo\\Bar\\Baz', PathHelper::toNamespace('app/Foo/Bar/Baz'));
        $this->assertSame('App\\FooBar', PathHelper::toNamespace('app\\FooBar\\'));
        $this->assertSame('App\\FooBar', PathHelper::toNamespace('app\\FooBar\\File.php'));

        $this->assertSame('App\\Foo', PathHelper::toNamespace('/home/project-name/app/Foo/Bar.php', root: '/home/project-name'));
        $this->assertSame('App\\Foo', PathHelper::toNamespace('/home/project-name/app/Foo/Bar.php', root: '/home/project-name/'));

        // we don't support skill issues
        $this->assertSame('Home\ProjectName\App\Foo', PathHelper::toNamespace('/home/project-name/app/Foo/Bar.php'));
    }
}