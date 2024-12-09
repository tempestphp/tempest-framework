<?php

declare(strict_types=1);

namespace Tempest\Vite;

use Tempest\Container\Container;
use Tempest\Container\Exceptions\ContainerException;
use Tempest\Core\AppConfig;
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
    public const CLIENT_SCRIPT_PATH = '@vite/client';

    private static ?BridgeFile $bridgeFile = null;
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
     * Gets all tags.
     */
    public function getTags(?array $entrypoints = null): string
    {
        return implode('', $this->getTagsResolver()->resolveTags($entrypoints ?? $this->viteConfig->build->entrypoints));
    }

    private function getTagsResolver(): TagsResolver
    {
        try {
            return $this->container->get(TagsResolver::class);
        } catch (ContainerException) {
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
    }

    public function clearManifestCache(): self
    {
        static::$manifest = null;

        return $this;
    }

    private function getManifest(): Manifest
    {
        if (static::$manifest) {
            return static::$manifest;
        }

        if (! is_file($path = root_path('public', $this->viteConfig->build->buildDirectory, $this->viteConfig->build->manifest))) {
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
        if ($this->appConfig->environment->isTesting() && ! $this->viteConfig->testing) {
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

    private function getBridgeFile(): ?BridgeFile
    {
        if (static::$bridgeFile) {
            return static::$bridgeFile;
        }

        if (! $this->isDevelopmentServerRunning()) {
            return null;
        }

        $file = file_get_contents($this->getBridgeFilePath());
        $content = arr(json_decode($file, associative: true));

        return static::$bridgeFile = new BridgeFile(
            url: $content->get('url'),
        );
    }

    private function getBridgeFilePath(): string
    {
        return root_path('public', $this->viteConfig->build->bridgeFileName);
    }
}
