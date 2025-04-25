<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Vite;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversNothing;
use Tempest\Vite\Exceptions\DevelopmentServerNotRunningException;
use Tempest\Vite\Exceptions\ManifestNotFoundException;
use Tempest\Vite\TagsResolver\NullTagsResolver;
use Tempest\Vite\TagsResolver\TagsResolver;
use Tempest\Vite\Vite;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
#[CoversNothing]
final class ViteTesterTest extends FrameworkIntegrationTestCase
{
    public function test_does_not_throw_if_tag_resolution_is_disabled(): void
    {
        $this->vite->preventTagResolution();

        $tags = $this->container->get(Vite::class)->getTags();

        $this->assertEmpty($tags);
    }

    public function test_throws_if_dev_server_not_running_with_tags_resolution(): void
    {
        $this->expectException(DevelopmentServerNotRunningException::class);

        $this->vite->allowTagResolution();
        $this->vite->preventUsingManifest();

        $this->container->get(Vite::class)->getTags();
    }

    public function test_throws_if_manifest_not_found_with_tags_resolution(): void
    {
        $this->expectException(ManifestNotFoundException::class);

        $this->vite->allowTagResolution();
        $this->vite->allowUsingManifest();

        $this->container->get(Vite::class)->getTags();
    }

    public function test_call_creates_specified_files(): void
    {
        /** @var string|null */
        $path = null;

        $this->vite->call(
            callback: function (string $bridgeFilePath) use (&$path): void {
                $path = $bridgeFilePath;

                $this->assertTrue(is_file($bridgeFilePath));
                $this->assertEquals('{"url":"http://localhost:5173"}', file_get_contents($bridgeFilePath));
            },
            files: [
                'public/vite-tempest' => ['url' => 'http://localhost:5173'],
            ],
            root: __DIR__ . '/Fixtures/tmp',
        );

        $this->assertNotNull($path);
        $this->assertFalse(is_file($path));
    }

    public function test_call_requires_root(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->vite->call(
            callback: fn () => null,
            files: [],
        );
    }

    public function test_retains_tags_resolver(): void
    {
        $this->container->register(TagsResolver::class, fn () => new NullTagsResolver());

        $this->vite->call(
            callback: fn () => null,
            files: [],
            root: __DIR__ . '/Fixtures/tmp',
        );

        $this->assertInstanceOf(NullTagsResolver::class, $this->container->get(TagsResolver::class));
    }
}
