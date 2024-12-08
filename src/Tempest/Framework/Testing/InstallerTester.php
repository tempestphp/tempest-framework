<?php

declare(strict_types=1);

namespace Tempest\Framework\Testing;

use PHPUnit\Framework\Assert;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tempest\Container\Container;
use Tempest\Core\Composer;
use Tempest\Core\ComposerNamespace;
use Tempest\Core\Kernel;
use function Tempest\path;

final class InstallerTester
{
    private string $root;

    public function __construct(
        private readonly Container $container,
    ) {
    }

    public function configure(string $root, ComposerNamespace $mainNamespace): self
    {
        $this->root = $root;
        $this->container->get(Kernel::class)->root = $root;
        $this->container->get(Composer::class)->setMainNamespace($mainNamespace);

        if (! is_dir($this->root)) {
            mkdir($this->root, recursive: true);
        }

        if (! is_dir($mainNamespace->path)) {
            mkdir($mainNamespace->path, recursive: true);
        }

        return $this;
    }

    public function setRoot(string $root): self
    {
        $this->container->get(Kernel::class)->root = $root;

        return $this;
    }

    public function path(string $path): string
    {
        return path($this->root, $path)->toString();
    }

    public function put(string $path, string $contents): self
    {
        $path = $this->path($path);

        $dir = dirname($path);

        if (! is_dir($dir)) {
            mkdir($dir, recursive: true);
        }

        file_put_contents($path, $contents);

        return $this;
    }

    public function get(string $path): string
    {
        return file_get_contents($this->path($path));
    }

    public function assertFileExists(string $path, ?string $content = null): self
    {
        Assert::assertFileExists(
            $this->path($path),
            sprintf('File "%s" does not exist', $path),
        );

        if ($content) {
            $this->assertFileContains($path, $content);
        }

        return $this;
    }

    public function assertFileContains(string $path, string $search): self
    {
        Assert::assertStringContainsString(
            $search,
            $this->get($path),
            sprintf("File %s does not contain:\n %s", $path, $search),
        );

        return $this;
    }

    public function assertFileNotContains(string $path, string $search): self
    {
        Assert::assertStringNotContainsString(
            $search,
            $this->get($path),
            sprintf("File %s contains something it shouldn't:\n %s", $path, $search),
        );

        return $this;
    }

    public function clean(): void
    {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->root, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($files as $file) {
            $file->isDir()
                ? @rmdir($file->getRealPath())
                : @unlink($file->getRealPath());
        }

        @rmdir($this->root);
    }
}
