<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Core;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Core\Composer;
use Tempest\Core\KernelException;
use Tempest\Core\ShellExecutors\NullShellExecutor;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class ComposerTest extends FrameworkIntegrationTestCase
{
    private function initializeComposer(array $composer): Composer
    {
        file_put_contents(__DIR__ . '/../../Fixtures/Core/Composer/composer.json', json_encode($composer, JSON_PRETTY_PRINT));

        return new Composer(
            root: realpath(__DIR__ . '/../../Fixtures/Core/Composer'),
            executor: new NullShellExecutor(),
        )->load();
    }

    #[Test]
    public function takes_first_namespace_as_main(): void
    {
        $composer = $this->initializeComposer([
            'autoload' => [
                'psr-4' => [
                    'App\\' => 'app/',
                ],
            ],
        ]);

        $this->assertSame('App\\', $composer->mainNamespace->namespace);
        $this->assertSame('app/', $composer->mainNamespace->path);
        $this->assertCount(1, $composer->namespaces);
    }

    #[Test]
    public function test_sorts_namespaces_by_path_length(): void
    {
        $composer = $this->initializeComposer([
            'autoload' => [
                'psr-4' => [
                    'Baz\\' => 'src/Module/Bar/Baz',
                    'Module\\Foo\\' => 'src/Module/Foo/',
                    'Module\\' => 'src/Module/',
                ],
            ],
        ]);

        $this->assertSame('Module\\', $composer->namespaces[0]->namespace);
        $this->assertSame('Module\\Foo\\', $composer->namespaces[1]->namespace);
        $this->assertSame('Baz\\', $composer->namespaces[2]->namespace);
    }

    #[Test]
    public function test_sorts_main_namespace_first(): void
    {
        $composer = $this->initializeComposer([
            'autoload' => [
                'psr-4' => [
                    'Module\\' => 'source/Module',
                    'App\\' => 'source/App',
                    'Test\\' => 'tests',
                ],
            ],
        ]);

        $this->assertSame('App\\', $composer->mainNamespace->namespace);
        $this->assertSame('App\\', $composer->namespaces[0]->namespace);
        $this->assertSame('Test\\', $composer->namespaces[1]->namespace);
        $this->assertSame('Module\\', $composer->namespaces[2]->namespace);
    }

    #[Test]
    public function test_loads_composer_class_with_multiple_namespaces(): void
    {
        $composer = $this->initializeComposer([
            'autoload' => [
                'psr-4' => [
                    'Module\\' => 'src/Module/',
                    'Module\\Foo\\' => 'src/Module/Foo/',
                    'Module\\Bar\\' => 'src/Module/Bar/',
                ],
            ],
        ]);

        $this->assertSame('Module\\', $composer->mainNamespace->namespace);
        $this->assertSame('src/Module/', $composer->mainNamespace->path);

        $this->assertCount(3, $composer->namespaces);

        $this->assertSame('Module\\', $composer->namespaces[0]->namespace);
        $this->assertSame('Module\\Foo\\', $composer->namespaces[1]->namespace);
        $this->assertSame('Module\\Bar\\', $composer->namespaces[2]->namespace);
    }

    #[Test]
    public function takes_app_namespace_in_priority(): void
    {
        $composer = $this->initializeComposer([
            'autoload' => [
                'psr-4' => [
                    'Config\\' => 'config/',
                    'App\\' => 'app/',
                ],
            ],
        ]);

        $this->assertSame('App\\', $composer->mainNamespace->namespace);
        $this->assertSame('app/', $composer->mainNamespace->path);
    }

    #[Test]
    public function takes_src_namespace_in_priority(): void
    {
        $composer = $this->initializeComposer([
            'autoload' => [
                'psr-4' => [
                    'Config\\' => 'config/',
                    'App\\' => 'src/',
                ],
            ],
        ]);

        $this->assertSame('App\\', $composer->mainNamespace->namespace);
        $this->assertSame('src/', $composer->mainNamespace->path);
    }

    #[Test]
    public function errors_without_composer_file(): void
    {
        $this->expectException(KernelException::class);

        new Composer(root: __DIR__, executor: new NullShellExecutor())->load();
    }
}
