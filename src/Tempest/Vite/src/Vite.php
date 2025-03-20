<?php

declare(strict_types=1);

namespace Tempest\Vite;

use Tempest\Container\Container;
use Tempest\Core\AppConfig;
use Tempest\Vite\Exceptions\DevelopmentServerNotRunningException;
use Tempest\Vite\Exceptions\ManifestNotFoundException;
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
        private readonly AppConfig $appConfig,
        private readonly ViteConfig $viteConfig,
        private readonly Container $container,
        private readonly TagCompiler $tagCompiler,
    ) {
    }

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
            throw new ManifestNotFoundException($path);
        }

        return static::$manifest = Manifest::fromArray(json_decode(
            json: file_get_contents($path),
            associative: true,
            flags: JSON_THROW_ON_ERROR,
        ));
    }

    private function shouldUseManifest(): bool
    {
        if ($this->appConfig->environment->isTesting() && ! $this->viteConfig->useManifestDuringTesting) {
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
            throw new DevelopmentServerNotRunningException();
        }

        $file = file_get_contents($this->getBridgeFilePath());
        $content = arr(json_decode($file, associative: true));

        return static::$bridgeFile = new ViteBridgeFile(
            url: $content->get('url'),
        );
    }

    private function getBridgeFilePath(): string
    {
        return root_path('public', $this->viteConfig->bridgeFileName);
    }
}
