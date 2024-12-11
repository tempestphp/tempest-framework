<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Vite;

use Tempest\Vite\TagCompiler\TagCompiler;
use Tempest\Vite\TagsResolver\DevelopmentTagsResolver;
use Tempest\Vite\ViteBridgeFile;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class DevelopmentTagsResolverTest extends FrameworkIntegrationTestCase
{
    public function test_resolve_tags(): void
    {
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
    }
}
