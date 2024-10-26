<?php

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
    private string $installPath;

    public function __construct(
        private readonly Container $container,
    ) {}

    public function setNamespace(ComposerNamespace $namespace): self
    {
        $this->installPath = $namespace->path;
        $this->container->get(Kernel::class)->root = $namespace->path;
        $this->container->get(Composer::class)->setMainNamespace($namespace);

        if (! is_dir($this->installPath)) {
            mkdir($this->installPath, recursive: true);
        }

        return $this;
    }

    public function path(string $path): string
    {
        return path($this->installPath, $path);
    }

    public function get(string $path): string
    {
        return file_get_contents($this->path($path));
    }

    public function assertFileExists(string $path): self
    {
        Assert::assertFileExists($this->path($path));

        return $this;
    }

    public function assertFileContains(string $path, string $search): self
    {
        Assert::assertStringContainsString($search, $this->get($path));

        return $this;
    }

    public function assertFileNotContains(string $path, string $search): self
    {
        Assert::assertStringNotContainsString($search, $this->get($path));

        return $this;
    }

    public function clean(): void
    {
        if ($this->installPath === null) {
            return;
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->installPath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($files as $file) {
            $file->isDir()
                ? @rmdir($file->getRealPath())
                : @unlink($file->getRealPath());
        }

        @rmdir($this->installPath);
    }
}