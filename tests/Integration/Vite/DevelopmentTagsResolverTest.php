<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Vite;

use Tempest\Core\Composer;
use Tempest\Core\ComposerNamespace;
use Tempest\Vite\Exceptions\FileSystemEntrypointNotFoundException;
use Tempest\Vite\TagCompiler\TagCompiler;
use Tempest\Vite\TagsResolver\DevelopmentTagsResolver;
use Tempest\Vite\ViteBridgeFile;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class DevelopmentTagsResolverTest extends FrameworkIntegrationTestCase
{
    use HasFixtures;

    protected function setUp(): void
    {
        parent::setUp();

        $this->vite->setRootDirectory(__DIR__ . '/Fixtures/tmp');
    }

    public function test_resolve_tags(): void
    {
        $this->vite->call(
            files: [
                'src/main.ts' => '',
                'src/foo.ts' => '',
                'src/tailwind.css' => '',
            ],
            callback: function () {
                $resolver = new DevelopmentTagsResolver(
                    bridgeFile: new ViteBridgeFile(url: 'http://localhost'),
                    tagCompiler: $this->container->get(TagCompiler::class),
                );

                $this->assertSame(
                    expected: [
                        '<script type="module" src="http://localhost/@vite/client"></script>',
                        '<script type="module" src="http://localhost/src/main.ts"></script>',
                        '<script type="module" src="http://localhost/src/foo.ts"></script>',
                        '<link rel="stylesheet" href="http://localhost/src/tailwind.css" />',
                    ],
                    actual: $resolver->resolveTags(['src/main.ts', 'src/foo.ts', 'src/tailwind.css']),
                );
            },
        );
    }

    public function test_throw_if_entrypoint_not_found(): void
    {
        $this->expectException(FileSystemEntrypointNotFoundException::class);

        $this->vite->call(
            files: [
                'src/main.ts' => '',
            ],
            callback: function () {
                $resolver = new DevelopmentTagsResolver(
                    bridgeFile: new ViteBridgeFile(url: 'http://localhost'),
                    tagCompiler: $this->container->get(TagCompiler::class),
                );

                $resolver->resolveTags(['src/main.ts', 'src/foo.ts']);
            },
        );
    }

    public function test_automatically_converts_relative_paths(): void
    {
        $this->container->get(Composer::class)->mainNamespace = new ComposerNamespace(
            namespace: 'App',
            path: 'src/',
        );

        $this->vite->call(
            files: [
                'src/main.ts' => '',
                'src/foo.ts' => '',
                'src/bar/baz.ts' => '',
            ],
            callback: function () {
                $resolver = new DevelopmentTagsResolver(
                    bridgeFile: new ViteBridgeFile(url: 'http://localhost'),
                    tagCompiler: $this->container->get(TagCompiler::class),
                );

                $this->assertSame(
                    expected: [
                        '<script type="module" src="http://localhost/@vite/client"></script>',
                        '<script type="module" src="http://localhost/src/main.ts"></script>',
                        '<script type="module" src="http://localhost/src/foo.ts"></script>',
                        '<script type="module" src="http://localhost/src/bar/baz.ts"></script>',
                    ],
                    actual: $resolver->resolveTags(['src/main.ts', 'src/foo.ts', './src/bar/baz.ts']),
                );
            },
        );
    }
}
