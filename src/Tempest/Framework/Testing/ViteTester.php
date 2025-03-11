<?php

declare(strict_types=1);

namespace Tempest\Framework\Testing;

use InvalidArgumentException;
use Tempest\Container\Container;
use Tempest\Container\Exceptions\ContainerException;
use Tempest\Core\Kernel;
use Tempest\Filesystem\LocalFilesystem;
use Tempest\Vite\TagsResolver\NullTagsResolver;
use Tempest\Vite\TagsResolver\TagsResolver;
use Tempest\Vite\Vite;
use Tempest\Vite\ViteConfig;

final class ViteTester
{
    private ?string $root = null;

    public function __construct(
        private readonly Container $container,
    ) {
    }

    /**
     * Sets the root directory for subsequent {@see \Tempest\Framework\Testing\ViteTester::call()} calls.
     */
    public function setRootDirectory(string $directory): self
    {
        $this->root = $directory;

        return $this;
    }

    /**
     * Clears the manifest and bridge cache.
     */
    public function clearCaches(): self
    {
        $this->container
            ->get(Vite::class)
            ->clearManifestCache()
            ->clearBridgeCache();

        return $this;
    }

    /**
     * Instructs Vite to not resolve tags during tests.
     */
    public function preventTagResolution(): self
    {
        $this->container->register(TagsResolver::class, fn () => new NullTagsResolver());

        return $this;
    }

    /**
     * Allows Vite to resolve tags normally.
     */
    public function allowTagResolution(): self
    {
        $this->container->unregister(TagsResolver::class);

        return $this;
    }

    /**
     * Allows Vite to try reading the manifest during tests.
     */
    public function allowUsingManifest(): self
    {
        $config = $this->container->get(ViteConfig::class);
        $config->useManifestDuringTesting = true;

        $this->container->config($config);

        return $this;
    }

    /**
     * Instructs Vite to not read the manifest during tests.
     */
    public function preventUsingManifest(): self
    {
        $config = $this->container->get(ViteConfig::class);
        $config->useManifestDuringTesting = false;

        $this->container->config($config);

        return $this;
    }

    /**
     * Creates a temporary environment with the specified `$root` and `$files`, so Vite can read a manifest or a bridge file.
     *
     * ```
     * $this->vite->callWithFiles(
     *     callback: function (string $bridgeFilePath): void {
     *         // Do something with Vite
     *         // $vite = $this->container->get(Vite::class);
     *     },
     *     files: [
     *         'public/vite-tempest' => ['url' => 'http://localhost:5173'],
     *         'public/build/manifest.json' => [ (manifest content) ],
     *     ],
     *     root: __DIR__ . '/tmp',
     * );
     * ```
     */
    public function call(callable $callback, array $files, bool $manifest = false, ?string $root = null): void
    {
        $actualViteConfig = $this->container->get(ViteConfig::class);
        $temporaryViteConfig = clone $actualViteConfig;

        if (! $manifest) {
            $temporaryViteConfig->useManifestDuringTesting = true;
        }

        $actualRootDirectory = $this->container->get(Kernel::class)->root;
        $temporaryRootDirectory = $root ?? $this->root ?? throw new InvalidArgumentException('`callWithFiles` requires a temporary root directory.');

        try {
            $tagsResolver = $this->container->get(TagsResolver::class);
        } catch (ContainerException) {
            $tagsResolver = null;
        }

        $filesystem = new LocalFilesystem();
        $filesystem->deleteDirectory($temporaryRootDirectory, recursive: true);
        $filesystem->ensureDirectoryExists($temporaryRootDirectory);

        $paths = [];

        foreach ($files as $path => $content) {
            $path = "{$temporaryRootDirectory}/{$path}";
            $paths[] = $path;
            $filesystem->ensureDirectoryExists(dirname($path));
            $filesystem->write($path, is_array($content) ? json_encode($content, flags: JSON_UNESCAPED_SLASHES) : $content);
        }

        $this->container->get(Kernel::class)->root = $temporaryRootDirectory;
        $this->container->config($temporaryViteConfig);
        $this->container->unregister(TagsResolver::class);
        $callback(...$paths);
        $this->container->get(Kernel::class)->root = $actualRootDirectory;
        $this->container->config($actualViteConfig);

        if ($tagsResolver) {
            $this->container->register(TagsResolver::class, fn () => $tagsResolver);
        }

        $filesystem->deleteDirectory($temporaryRootDirectory, recursive: true);
    }
}
