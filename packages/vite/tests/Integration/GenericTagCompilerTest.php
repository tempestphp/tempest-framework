<?php

declare(strict_types=1);

namespace Tempest\Vite\Tests\Integration;

use Tempest\Vite\Manifest\Chunk;
use Tempest\Vite\TagCompiler\GenericTagCompiler;
use Tempest\Vite\TagCompiler\TagCompiler;
use Tempest\Vite\ViteConfig;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class GenericTagCompilerTest extends FrameworkIntegrationTestCase
{
    public function test_wired(): void
    {
        $compiler = $this->container->get(TagCompiler::class);

        $this->assertInstanceOf(GenericTagCompiler::class, $compiler);
    }

    public function test_generate_script_tag(): void
    {
        $generator = $this->container->get(GenericTagCompiler::class);
        $tag = $generator->compileScriptTag('/build/main.js', $this->createChunk());

        $this->assertSame('<script type="module" src="/build/main.js"></script>', $tag);
    }

    public function test_generate_legacy_script_tag(): void
    {
        $generator = $this->container->get(GenericTagCompiler::class);
        $tag = $generator->compileScriptTag('/build/main.js', $this->createChunk(legacy: true));

        $this->assertSame('<script nomodule src="/build/main.js"></script>', $tag);
    }

    public function test_generate_legacy_script_tag_with_polyfills(): void
    {
        $generator = $this->container->get(GenericTagCompiler::class);
        $tag = $generator->compileScriptTag('/build/main.js', $this->createChunk(legacy: true, src: 'vite/legacy-polyfills'));

        $this->assertStringContainsString('<script nomodule>', $tag);
        $this->assertStringContainsString('<script type="module">', $tag);
        $this->assertStringContainsString('<script nomodule id="vite-legacy-polyfill" src="/build/main.js"></script>', $tag);
    }

    public function test_generate_style_tag(): void
    {
        $generator = $this->container->get(GenericTagCompiler::class);
        $tag = $generator->compileStyleTag('/build/main.css', $this->createChunk());

        $this->assertSame('<link rel="stylesheet" href="/build/main.css" />', $tag);
    }

    public function test_generate_preload_tag(): void
    {
        $generator = $this->container->get(GenericTagCompiler::class);
        $tag = $generator->compilePreloadTag('/build/main.js', $this->createChunk());

        $this->assertSame('<link rel="modulepreload" href="/build/main.js" />', $tag);
    }

    public function test_generate_script_tag_with_integrity(): void
    {
        $generator = $this->container->get(GenericTagCompiler::class);
        $tag = $generator->compileScriptTag('/build/main.js', $this->createChunk(integrity: 'sha256-abc123'));

        $this->assertSame('<script type="module" src="/build/main.js" integrity="sha256-abc123" crossorigin="anonymous"></script>', $tag);
    }

    public function test_generate_style_tag_with_integrity(): void
    {
        $generator = $this->container->get(GenericTagCompiler::class);
        $tag = $generator->compileStyleTag('/build/main.css', $this->createChunk(integrity: 'sha256-abc123'));

        $this->assertSame('<link rel="stylesheet" href="/build/main.css" integrity="sha256-abc123" crossorigin="anonymous" />', $tag);
    }

    public function test_generate_script_tag_with_nonce(): void
    {
        $this->container->config(new ViteConfig(
            nonce: 'expected-nonce',
        ));

        $generator = $this->container->get(GenericTagCompiler::class);
        $tag = $generator->compileScriptTag('/build/main.js', $this->createChunk());

        $this->assertSame('<script type="module" src="/build/main.js" nonce="expected-nonce"></script>', $tag);
    }

    public function test_generate_style_tag_with_nonce(): void
    {
        $this->container->config(new ViteConfig(
            nonce: 'expected-nonce',
        ));

        $generator = $this->container->get(GenericTagCompiler::class);
        $tag = $generator->compileStyleTag('/build/main.css', $this->createChunk());

        $this->assertSame('<link rel="stylesheet" href="/build/main.css" nonce="expected-nonce" />', $tag);
    }

    public function test_generate_script_tag_with_content(): void
    {
        $this->container->config(new ViteConfig(
            nonce: 'expected-nonce',
        ));

        $generator = $this->container->get(GenericTagCompiler::class);
        $tag = $generator->compilePrefetchTag('console.log("Hello, world!")', $this->createChunk());

        $this->assertSame('<script nonce="expected-nonce">console.log("Hello, world!")</script>', $tag);
    }

    private function createChunk(?string $src = null, bool $entry = true, ?string $integrity = null, bool $legacy = false): Chunk
    {
        return new Chunk(
            src: $src ?? 'main.ts',
            file: 'main.js',
            isDynamicEntry: true,
            isLegacyEntry: $legacy,
            isEntry: $entry,
            css: [],
            imports: [],
            dynamicImports: [],
            assets: [],
            integrity: $integrity,
        );
    }
}
