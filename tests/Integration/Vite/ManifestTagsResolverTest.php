<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Vite;

use Tempest\Vite\Manifest\Manifest;
use Tempest\Vite\TagCompiler\TagCompiler;
use Tempest\Vite\TagsResolver\ManifestTagsResolver;
use Tempest\Vite\ViteConfig;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class ManifestTagsResolverTest extends FrameworkIntegrationTestCase
{
    use HasFixtures;

    public function test_resolve_script(): void
    {
        $resolver = new ManifestTagsResolver(
            viteConfig: $this->container->get(ViteConfig::class),
            tagCompiler: $this->container->get(TagCompiler::class),
            manifest: Manifest::fromArray($this->fixture('simple-manifest.json')),
        );

        $this->assertSame(
            expected: [
                '<script type="module" src="build/assets/main-YJD4Cw3J.js"></script>',
            ],
            actual: $resolver->resolveTags(['src/main.ts']),
        );
    }

    public function test_resolve_script_with_css(): void
    {
        $resolver = new ManifestTagsResolver(
            viteConfig: $this->container->get(ViteConfig::class),
            tagCompiler: $this->container->get(TagCompiler::class),
            manifest: Manifest::fromArray($this->fixture('simple-manifest-with-css.json')),
        );

        $this->assertSame(
            expected: [
                '<link rel="stylesheet" href="build/assets/main-DObprJ9K.css" />',
                '<script type="module" src="build/assets/main-CK61jJwL.js"></script>',
            ],
            actual: $resolver->resolveTags(['src/main.ts']),
        );
    }
}
