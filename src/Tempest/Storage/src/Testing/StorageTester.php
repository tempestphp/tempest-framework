<?php

namespace Tempest\Storage\Testing;

use Closure;
use DateTimeInterface;
use League\Flysystem\Config;
use League\Flysystem\UrlGeneration\TemporaryUrlGenerator;
use PHPUnit\Framework\Assert;
use Tempest\Container\Container;
use Tempest\Storage\Storage;
use UnitEnum;

use function Tempest\Support\Str\to_kebab_case;

final class StorageTester
{
    private ?TestingStorage $storage = null;

    public function __construct(
        private readonly Container $container,
    ) {}

    public function fake(null|string|UnitEnum $tag = null): void
    {
        $this->storage = new TestingStorage(match (true) {
            is_string($tag) => to_kebab_case($tag),
            $tag instanceof UnitEnum => to_kebab_case($tag->name),
            default => 'default',
        });

        $this->storage->cleanDirectory();

        $this->container->singleton(Storage::class, $this->storage, $tag);
    }

    public function createTemporaryUrlsUsing(Closure $closure): void
    {
        $generator = new class($closure) implements TemporaryUrlGenerator {
            public function __construct(
                private readonly Closure $closure,
            ) {}

            public function temporaryUrl(string $path, DateTimeInterface $expiresAt, Config $config): string
            {
                return ($this->closure)($path, $expiresAt);
            }
        };

        $this->storage->setTemporaryUrlGenerator($generator);
    }

    public function assertFileExists(string $path): void
    {
        $storage = $this->container->get(Storage::class);

        Assert::assertTrue($storage->fileExists($path), sprintf('File `%s` does not exist.', $path));
    }

    public function assertSee(string $path, string $contents): void
    {
        $storage = $this->container->get(Storage::class);

        $this->assertFileExists($path);
        Assert::assertStringContainsString($contents, $storage->read($path), sprintf('File `%s` does not contain `%s`.', $path, $contents));
    }

    public function assertDontSee(string $path, string $contents): void
    {
        $storage = $this->container->get(Storage::class);

        $this->assertFileExists($path);
        Assert::assertStringNotContainsString($contents, $storage->read($path), sprintf('File `%s` contains `%s`.', $path, $contents));
    }

    public function assertFileDoesNotExist(string $path): void
    {
        $storage = $this->container->get(Storage::class);

        Assert::assertFalse($storage->fileExists($path), sprintf('File `%s` exists.', $path));
    }

    public function assertDirectoryExists(string $path): void
    {
        $storage = $this->container->get(Storage::class);

        Assert::assertTrue($storage->directoryExists($path), sprintf('Directory `%s` does not exist.', $path));
    }

    public function assertDirectoryEmpty(string $path = ''): void
    {
        $storage = $this->container->get(Storage::class);

        $this->assertDirectoryExists($path);
        Assert::assertEmpty($storage->list($path)->toArray(), sprintf('Directory `%s` is not empty.', $path));
    }

    public function assertDirectoryNotEmpty(string $path): void
    {
        $storage = $this->container->get(Storage::class);

        $this->assertDirectoryExists($path);
        Assert::assertNotEmpty($storage->list($path)->toArray(), sprintf('Directory `%s` is empty.', $path));
    }

    public function assertDirectoryDoesNotExist(string $path): void
    {
        $storage = $this->container->get(Storage::class);

        Assert::assertFalse($storage->directoryExists($path), sprintf('Directory `%s` exists.', $path));
    }

    public function assertFileOrDirectoryExists(string $path): void
    {
        $storage = $this->container->get(Storage::class);

        Assert::assertTrue($storage->fileOrDirectoryExists($path), sprintf('File or directory `%s` does not exist.', $path));
    }

    public function assertFileOrDirectoryDoesNotExist(string $path): void
    {
        $storage = $this->container->get(Storage::class);

        Assert::assertFalse($storage->fileOrDirectoryExists($path), sprintf('File or directory `%s` exists.', $path));
    }
}
