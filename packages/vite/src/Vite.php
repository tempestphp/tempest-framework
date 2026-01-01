<?php

declare(strict_types=1);

namespace Tempest\Vite;

use Tempest\Container\Container;
use Tempest\Core\Environment;
use Tempest\Support\Filesystem;
use Tempest\Support\Json;
use Tempest\Vite\Exceptions\DevelopmentServerWasNotRunning;
use Tempest\Vite\Exceptions\ManifestWasNotFound;
use Tempest\Vite\Manifest\Manifest;
use Tempest\Vite\TagCompiler\TagCompiler;
use Tempest\Vite\TagsResolver\DevelopmentTagsResolver;
use Tempest\Vite\TagsResolver\ManifestTagsResolver;
use Tempest\Vite\TagsResolver\TagsResolver;

use function Tempest\root_path;
use function Tempest\Support\arr;

final class Vite
{
    public const string CLIENT_SCRIPT_PATH = '@vite/client';

    private static ?ViteBridgeFile $bridgeFile = null;

    private static ?Manifest $manifest = null;

    public function __construct(
        private readonly Environment $environment,
        private readonly ViteConfig $viteConfig,
        private readonly Container $container,
        private readonly TagCompiler $tagCompiler,
    ) {}

    /**
     * Sets the Content Security Policy nonce to be used by Vite.
     */
    public function setNonce(string $nonce): void
    {
        $this->viteConfig->nonce = $nonce;
        $this->container->config($this->viteConfig);
    }

    /**
     * Gets the tags for the specified or configured `$entrypoints`.
     */
    public function getTags(?array $entrypoints = null): array
    {
        return $this->getTagsResolver()->resolveTags(
            array_filter($entrypoints ?: []) ?: array_filter($this->viteConfig->entrypoints ?: []),
        );
    }

    /**
     * Clears the manifest cache for this request.
     */
    public function clearManifestCache(): self
    {
        static::$manifest = null;

        return $this;
    }

    /**
     * Clears the bridge file cache for this request.
     */
    public function clearBridgeCache(): self
    {
        static::$bridgeFile = null;

        return $this;
    }

    private function getTagsResolver(): TagsResolver
    {
        if ($this->container->has(TagsResolver::class)) {
            return $this->container->get(TagsResolver::class);
        }

        return match ($this->shouldUseManifest()) {
            true => new ManifestTagsResolver(
                viteConfig: $this->viteConfig,
                tagCompiler: $this->tagCompiler,
                manifest: $this->getManifest(),
            ),
            false => new DevelopmentTagsResolver(
                bridgeFile: $this->getBridgeFile(),
                tagCompiler: $this->tagCompiler,
            ),
        };
    }

    private function getManifest(): Manifest
    {
        if (static::$manifest !== null) {
            return static::$manifest;
        }

        if (! is_file($path = root_path('public', $this->viteConfig->buildDirectory, $this->viteConfig->manifest))) {
            throw new ManifestWasNotFound($path);
        }

        return static::$manifest = Manifest::fromArray(Json\decode(
            Filesystem\read_file($path),
        ));
    }

    private function shouldUseManifest(): bool
    {
        if ($this->environment->isTesting() && ! $this->viteConfig->useManifestDuringTesting) {
            return false;
        }

        if ($this->isDevelopmentServerRunning()) {
            return false;
        }

        return true;
    }

    private function isDevelopmentServerRunning(): bool
    {
        return is_file($this->getBridgeFilePath());
    }

    private function getBridgeFile(): ViteBridgeFile
    {
        if (static::$bridgeFile !== null) {
            return static::$bridgeFile;
        }

        if (! $this->isDevelopmentServerRunning()) {
            throw new DevelopmentServerWasNotRunning();
        }

        $file = Filesystem\read_file($this->getBridgeFilePath());
        $content = arr(Json\decode($file));

        return static::$bridgeFile = new ViteBridgeFile(
            url: $content->get('url'),
            needsReactRefresh: $content->get('needsReactRefresh', default: false),
        );
    }

    private function getBridgeFilePath(): string
    {
        return root_path('public', $this->viteConfig->bridgeFileName);
    }
}
