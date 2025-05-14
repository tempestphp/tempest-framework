<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Vite;

use Tempest\View\ViewCache;
use Tempest\Vite\ViteConfig;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class ViteTagsComponentTest extends FrameworkIntegrationTestCase
{
    use HasFixtures;

    protected function setUp(): void
    {
        parent::setUp();

        $this->vite->setRootDirectory(__DIR__ . '/Fixtures/tmp');
    }

    public function test_dev_entrypoint(): void
    {
        $this->vite->call(
            callback: function (): void {
                $this->container->config(new ViteConfig(
                    entrypoints: ['src/foo.ts', 'src/bar.css'],
                ));

                $html = $this->render(<<<'HTML'
                <html lang="en">
                <head>
                    <x-vite-tags entrypoint="src/foo.ts" />
                </head>
                <body>Foo</body>
                </html>
                HTML);

                $this->assertSnippetsMatch(
                    expected: <<<HTML
                    <html lang="en"><head><script type="module" src="http://localhost:5173/@vite/client"></script><script type="module" src="http://localhost:5173/src/foo.ts"></script></head><body>Foo
                    </body></html>
                    HTML,
                    actual: $html,
                );
            },
            files: [
                'public/vite-tempest' => ['url' => 'http://localhost:5173'],
                'src/foo.ts' => '',
                'src/bar.css' => '',
            ],
        );
    }

    public function test_dev_entrypoints(): void
    {
        $this->vite->call(
            callback: function (): void {
                $this->container->config(new ViteConfig(
                    entrypoints: ['src/foo.ts', 'src/bar.css'],
                ));

                $html = $this->render(<<<'HTML'
                <html lang="en">
                <head>
                    <x-vite-tags :entrypoint="['src/foo.ts', 'src/bar.css']" />
                </head>
                <body>Foo</body>
                </html>
                HTML);

                $this->assertSnippetsMatch(
                    expected: <<<HTML
                    <html lang="en"><head><script type="module" src="http://localhost:5173/@vite/client"></script><script type="module" src="http://localhost:5173/src/foo.ts"></script><link rel="stylesheet" href="http://localhost:5173/src/bar.css" /></head><body>Foo
                    </body></html>
                    HTML,
                    actual: $html,
                );
            },
            files: [
                'public/vite-tempest' => ['url' => 'http://localhost:5173'],
                'src/foo.ts' => '',
                'src/bar.css' => '',
            ],
        );
    }

    public function test_dev_entrypoints_from_config(): void
    {
        $this->vite->call(
            callback: function (): void {
                $this->container->config(new ViteConfig(
                    entrypoints: ['src/foo.ts', 'src/bar.css'],
                ));

                $html = $this->render(<<<'HTML'
                <html lang="en">
                <head>
                    <x-vite-tags />
                </head>
                <body>Foo</body>
                </html>
                HTML);

                $this->assertSnippetsMatch(
                    expected: <<<HTML
                    <html lang="en"><head><script type="module" src="http://localhost:5173/@vite/client"></script><script type="module" src="http://localhost:5173/src/foo.ts"></script><link rel="stylesheet" href="http://localhost:5173/src/bar.css" /></head><body>Foo
                    </body></html>
                    HTML,
                    actual: $html,
                );
            },
            files: [
                'public/vite-tempest' => ['url' => 'http://localhost:5173'],
                'src/foo.ts' => '',
                'src/bar.css' => '',
            ],
        );
    }

    public function test_production_entrypoint_from_config(): void
    {
        $this->vite->call(
            callback: function (): void {
                $this->container->config(new ViteConfig(
                    useManifestDuringTesting: true,
                    entrypoints: ['src/foo.ts'],
                ));

                $html = $this->render(<<<'HTML'
                <html lang="en">
                <head>
                    <x-vite-tags />
                </head>
                <body></body>
                </html>
                HTML);

                $this->assertSnippetsMatch(<<<'HTML'
                <html lang="en"><head><script type="module" src="/build/assets/foo-YJD4Cw3J.js"></script></head><body></body></html>
                HTML, $html);
            },
            files: [
                'public/build/manifest.json' => $this->fixture('two-unrelated-entrypoints.json'),
            ],
        );
    }

    public function test_production_entrypoint(): void
    {
        $this->vite->call(
            callback: function (): void {
                $this->container->config(new ViteConfig(
                    useManifestDuringTesting: true,
                    entrypoints: ['src/foo.ts'],
                ));

                $html = $this->render(<<<'HTML'
                <html lang="en">
                <head>
                    <x-vite-tags entrypoint="src/bar.ts" />
                </head>
                <body></body>
                </html>
                HTML);

                $this->assertSnippetsMatch(<<<'HTML'
                <html lang="en"><head><script type="module" src="/build/assets/bar-WlXl03ld.js"></script></head><body></body></html>
                HTML, $html);
            },
            files: [
                'public/build/manifest.json' => $this->fixture('two-unrelated-entrypoints.json'),
            ],
        );
    }

    public function test_production_entrypoints(): void
    {
        $this->vite->call(
            callback: function (): void {
                $this->container->config(new ViteConfig(
                    useManifestDuringTesting: true,
                    entrypoints: ['src/foo.ts'],
                ));

                $html = $this->render(<<<'HTML'
                <html lang="en">
                <head>
                    <x-vite-tags :entrypoints="['src/bar.ts', 'src/foo.ts']" />
                </head>
                <body></body>
                </html>
                HTML);

                $this->assertSnippetsMatch(<<<'HTML'
                <html lang="en"><head><script type="module" src="/build/assets/bar-WlXl03ld.js"></script><script type="module" src="/build/assets/foo-YJD4Cw3J.js"></script></head><body></body></html>
                HTML, $html);
            },
            files: [
                'public/build/manifest.json' => $this->fixture('two-unrelated-entrypoints.json'),
            ],
        );
    }
}
