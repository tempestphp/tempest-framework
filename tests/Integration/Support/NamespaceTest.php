<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Support;

use Exception;
use PHPUnit\Framework\Attributes\Test;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Support\Namespace\to_base_class_name;
use function Tempest\Support\Namespace\to_main_namespace;
use function Tempest\Support\Namespace\to_namespace;
use function Tempest\Support\Namespace\to_registered_namespace;

/**
 * @internal
 */
final class NamespaceTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function path_to_namespace(): void
    {
        $this->assertSame('App', to_namespace('app/SomeNewClass.php'));
        $this->assertSame('App\\Foo\\Bar', to_namespace('app/Foo/Bar/SomeNewClass.php'));
        $this->assertSame('App\\Foo\\Bar\\Baz', to_namespace('app/Foo/Bar/Baz'));
        $this->assertSame('App\\FooBar', to_namespace('app\\FooBar\\'));
        $this->assertSame('App\\FooBar', to_namespace('app\\FooBar\\File.php'));

        $this->assertSame('App\\Foo', to_namespace('/home/project-name/app/Foo/Bar.php', root: '/home/project-name'));
        $this->assertSame('App\\Foo', to_namespace('/home/project-name/app/Foo/Bar.php', root: '/home/project-name/'));

        // we don't support skill issues
        $this->assertSame('Home\ProjectName\App\Foo', to_namespace('/home/project-name/app/Foo/Bar.php'));
    }

    #[Test]
    public function path_to_main_namespace(): void
    {
        $this->assertSame('Tempest\\Auth', to_main_namespace('src/Tempest/Auth/src/SomeNewClass.php'));
        $this->assertSame('Tempest\\Auth\\SomeDirectory', to_main_namespace('src/Tempest/Auth/src/SomeDirectory'));

        $this->assertSame('Tempest\\Auth\\Src\\Tempest\\OtherNamespace\\Src\\SomeDirectory', to_main_namespace('src/Tempest/OtherNamespace/src/SomeDirectory'));

        $this->assertSame('Tempest\\Auth\\Foo', to_main_namespace('Foo'));
        $this->assertSame('Tempest\\Auth\\Foo', to_main_namespace('Foo/Bar.php'));
    }

    #[Test]
    public function path_to_registered_namespace(): void
    {
        $this->assertSame('Tempest\\Vite', to_registered_namespace('src/Tempest/Vite/src/Vite.php'));
        $this->assertSame('Tempest\\Auth\\SomeDirectory', to_registered_namespace('src/Tempest/Auth/src/SomeDirectory'));
    }

    #[Test]
    public function paths_to_registered_namespace_with_unknown_namespace(): void
    {
        $this->expectException(Exception::class);

        to_registered_namespace('app/SomeNewClass.php');
    }

    #[Test]
    public function path_to_base_class_name(): void
    {
        $this->assertSame('Vite', to_base_class_name('src/Tempest/Vite/Vite.php'));
        $this->assertSame('Vite', to_base_class_name('app/Vite.php'));
        $this->assertSame('Vite', to_base_class_name('Vite.php'));

        $this->assertSame('Vite', to_base_class_name(\Tempest\Vite\Vite::class));
        $this->assertSame('Vite', to_base_class_name('Vite'));
    }
}
