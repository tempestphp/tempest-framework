<?php

declare(strict_types=1);

namespace Tempest\Framework\Testing;

use Tempest\Container\Container;
use Tempest\Core\Kernel;
use Tempest\Filesystem\LocalFilesystem;
use Tempest\Vite\Vite;
use Tempest\Vite\ViteConfig;

final class ViteTester
{
    private bool $shouldUseManifest = false;
    private bool $shouldCacheManifest = false;
    private ?string $root = null;

    public function __construct(
        private readonly Container $container,
    ) {
    }

    public function setRootDirectory(string $directory): self
    {
        $this->root = $directory;

        return $this;
    }

    public function withoutManifest(): self
    {
        $this->shouldUseManifest = false;

        return $this;
    }

    public function withManifest(bool $enable = true): self
    {
        $this->shouldUseManifest = $enable;

        return $this;
    }

    public function withManifestCache(bool $enable = true): self
    {
        $this->shouldCacheManifest = $enable;

        return $this;
    }

    public function callWithFiles(callable $callback, array $files): void
    {
        $vite = $this->container->get(Vite::class);
        $actualRootDirectory = $this->container->get(Kernel::class)->root;
        $temporaryRootDirectory = $this->root;
        $paths = [];

        $filesystem = new LocalFilesystem();
        $filesystem->deleteDirectory($temporaryRootDirectory, recursive: true);

        if (! $this->shouldCacheManifest) {
            $vite->clearManifestCache();
        }

        if (! $this->shouldUseManifest) {
            $config = $this->container->get(ViteConfig::class);
            $config->testing = true;
            $this->container->config($config);
        }

        foreach ($files as $path => $content) {
            $path = "{$temporaryRootDirectory}/{$path}";
            $paths[] = $path;
            $filesystem->ensureDirectoryExists(dirname($path));
            file_put_contents($path, json_encode($content, flags: JSON_UNESCAPED_SLASHES));
        }

        $this->container->get(Kernel::class)->root = $temporaryRootDirectory;
        $callback(...$paths);
        $this->container->get(Kernel::class)->root = $actualRootDirectory;

        @rmdir($temporaryRootDirectory);
    }
}
