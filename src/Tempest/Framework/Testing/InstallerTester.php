<?php

declare(strict_types=1);

namespace Tempest\Framework\Testing;

use PHPUnit\Framework\Assert;
use Tempest\Container\Container;
use Tempest\Core\Composer;
use Tempest\Core\FrameworkKernel;
use Tempest\Process\Testing\ProcessTester;
use Tempest\Support\Arr;
use Tempest\Support\Filesystem;
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

        Filesystem\ensure_directory_exists($this->root);
        Filesystem\ensure_directory_exists($namespace->path);

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

        Filesystem\write_file($path, $contents);

        return $this;
    }

    public function get(string $path): string
    {
        return Filesystem\read_file($this->path($path));
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

    public function assertDirectoryExists(string $path): self
    {
        Assert::assertDirectoryExists(
            directory: $this->path($path),
            message: sprintf('Directory "%s" does not exist', $path),
        );

        return $this;
    }

    public function assertFileContains(string $path, string|iterable $search): self
    {
        $content = $this->get($path);

        foreach (Arr\wrap($search) as $item) {
            Assert::assertStringContainsString(
                needle: $item,
                haystack: $content,
                message: sprintf("File %s does not contain:\n %s", $path, $item),
            );
        }

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
        Filesystem\delete_directory($this->root);
    }
}
