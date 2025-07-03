<?php

declare(strict_types=1);

namespace Tempest\Framework\Testing;

use PHPUnit\Framework\Assert;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tempest\Container\Container;
use Tempest\Core\Composer;
use Tempest\Core\FrameworkKernel;
use Tempest\Process\Testing\ProcessTester;
use Tempest\Support\Namespace\Psr4Namespace;

use function Tempest\Support\Path\to_absolute_path;

final class InstallerTester
{
    private string $root;

    private ProcessTester $process {
        get => $this->container->get(ProcessTester::class);
    }

    public function __construct(
        private readonly Container $container,
    ) {}

    public function configure(string $root, Psr4Namespace $namespace): self
    {
        $this->process->mockProcess('*', '');

        $this->root = $root;
        $this->container->get(FrameworkKernel::class)->root = $root;
        $this->container
            ->get(Composer::class)
            ->setMainNamespace($namespace)
            ->setNamespaces($namespace)
            ->setProcessExecutor($this->process->executor);

        if (! is_dir($this->root)) {
            mkdir($this->root, recursive: true);
        }

        if (! is_dir($namespace->path)) {
            mkdir($namespace->path, recursive: true);
        }

        return $this;
    }

    public function setRoot(string $root): self
    {
        $this->container->get(FrameworkKernel::class)->root = $root;

        return $this;
    }

    public function path(string $path): string
    {
        return to_absolute_path($this->root, $path);
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
            filename: $this->path($path),
            message: sprintf('File "%s" does not exist', $path),
        );

        if ($content) {
            $this->assertFileContains($path, $content);
        }

        return $this;
    }

    public function assertFileContains(string $path, string $search): self
    {
        Assert::assertStringContainsString(
            needle: $search,
            haystack: $this->get($path),
            message: sprintf("File %s does not contain:\n %s", $path, $search),
        );

        return $this;
    }

    public function assertFileNotContains(string $path, string $search): self
    {
        Assert::assertStringNotContainsString(
            needle: $search,
            haystack: $this->get($path),
            message: sprintf("File %s contains something it shouldn't:\n %s", $path, $search),
        );

        return $this;
    }

    public function assertCommandExecuted(string $command): self
    {
        $this->process->assertCommandRan($command);

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
