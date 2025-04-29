<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Core;

use PHPUnit\Framework\Attributes\TestWith;
use Tempest\Core\Composer;
use Tempest\Core\FrameworkKernel;
use Tempest\Drift\FrameworkIntegrationTestCase;
use Tempest\Support\Namespace\PathCouldNotBeMappedToNamespaceException;
use Tempest\Support\Namespace\Psr4Namespace;

use function Tempest\internal_storage_path;
use function Tempest\registered_namespace;
use function Tempest\root_path;
use function Tempest\src_namespace;
use function Tempest\src_path;
use function Tempest\Support\Path\is_absolute_path;

/**
 * @internal
 */
final class FunctionsTest extends FrameworkIntegrationTestCase
{
    public function test_src_path(): void
    {
        $this->container->get(Composer::class)->setMainNamespace(new Psr4Namespace('App\\', root_path('/app')));

        $this->assertSame(root_path('/app'), src_path());
        $this->assertSame(root_path('/app/User.php'), src_path('User.php'));
    }

    public function test_root_path(): void
    {
        $this->assertSame(root_path(), $this->root);
    }

    public function test_src_namespace_with_absolute_path(): void
    {
        $this->container->get(FrameworkKernel::class)->root = __DIR__ . '/tmp';
        $this->container->get(Composer::class)->setMainNamespace(new Psr4Namespace('App\\', 'app'));

        $this->assertSame('App', src_namespace(root_path('app')));
        $this->assertSame('App', src_namespace(root_path('app/Foo.php')));
        $this->assertSame('App\\Foo', src_namespace(root_path('app/Foo/BarBaz.php')));
        $this->assertSame('App\\Foo\\BarBaz', src_namespace(root_path('app/Foo/BarBaz')));

        $this->assertSame('App', src_namespace(src_path()));
        $this->assertSame('App', src_namespace(src_path('Foo.php')));
        $this->assertSame('App\\Foo', src_namespace(src_path('Foo/Bar.php')));
    }

    public function test_src_namespace_with_manual_paths(): void
    {
        $this->container->get(FrameworkKernel::class)->root = '/path/to/Auth/install';
        $this->container->get(Composer::class)->setMainNamespace(new Psr4Namespace('App\\', '/path/to/Auth/install/App'));

        $this->assertSame('App', src_namespace('/path/to/Auth/install/App'));
    }

    public function test_main_namespace_with_relative_path(): void
    {
        $this->container->get(Composer::class)->setMainNamespace(new Psr4Namespace('App\\', 'app'));

        $this->assertSame('App', src_namespace('app'));
        $this->assertSame('App', src_namespace('app/Foo.php'));
        $this->assertSame('App\\Foo', src_namespace('app/Foo/BarBaz.php'));
        $this->assertSame('App\\Foo\\BarBaz', src_namespace('app/Foo/BarBaz'));
    }

    #[TestWith([''])]
    #[TestWith(['Foo.php'])]
    #[TestWith(['src/Foo.php'])]
    public function test_exception_src_namespace(string $path): void
    {
        $this->expectException(PathCouldNotBeMappedToNamespaceException::class);
        $this->container->get(Composer::class)->setMainNamespace(new Psr4Namespace('App\\', 'app'));

        src_namespace($path);
    }

    public function test_registered_namespace(): void
    {
        $this->container
            ->get(Composer::class)
            ->setNamespaces([
                new Psr4Namespace('App\\', 'src/App'),
                new Psr4Namespace('Auth\\', 'src/Auth'),
            ]);

        $this->assertSame('Auth', registered_namespace('src/Auth'));
        $this->assertSame('Auth', registered_namespace('src/Auth/User.php'));
        $this->assertSame('Auth\\Http', registered_namespace('src/Auth/Http/UserController.php'));

        $this->assertSame('App', registered_namespace('src/App'));
        $this->assertSame('App', registered_namespace('src/App/HomeController.php'));
    }

    public function test_paths_are_absolute(): void
    {
        $this->assertTrue(is_absolute_path(internal_storage_path()));
        $this->assertTrue(is_absolute_path(root_path()));
        $this->assertTrue(is_absolute_path(src_path()));
    }
}
