<?php

namespace Tests\Tempest\Integration\Storage;

use DateTime;
use DateTimeInterface;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Tempest\Storage\Config\StorageConfig;
use Tempest\Storage\ForbiddenStorageUsageException;
use Tempest\Storage\MissingAdapterException;
use Tempest\Storage\Storage;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class StorageTesterTest extends FrameworkIntegrationTestCase
{
    public function test_basic(): void
    {
        $this->storage->fake();

        $storage = $this->container->get(Storage::class);
        $storage->write('foo.txt', 'bar');

        $this->storage->assertFileExists('foo.txt');
        $this->storage->assertSee('foo.txt', 'bar');
    }

    public function test_file_assertions(): void
    {
        $this->storage->fake();

        $storage = $this->container->get(Storage::class);
        $storage->write('foo.txt', 'bar');

        $this->storage->assertFileExists('foo.txt');
        $this->storage->assertFileOrDirectoryExists('foo.txt');
        $this->storage->assertDontSee('foo.txt', 'kdkdkd');

        $this->storage->assertFileDoesNotExist('do-not-exists.txt');
        $this->storage->assertFileOrDirectoryDoesNotExist('do-not-exists.txt');
    }

    public function test_directory_assertions(): void
    {
        $this->storage->fake();

        $storage = $this->container->get(Storage::class);
        $storage->write('foo/bar.txt', 'baz');
        $storage->createDirectory('empty');

        $this->storage->assertChecksumEquals('foo/bar.txt', '73feffa4b7f6bb68e44cf984c85f6e88');

        $this->storage->assertFileExists('foo/bar.txt');
        $this->storage->assertFileOrDirectoryExists('foo/bar.txt');
        $this->storage->assertDirectoryExists('foo');
        $this->storage->assertFileOrDirectoryExists('foo');
        $this->storage->assertDirectoryNotEmpty('foo');

        $this->storage->assertSee('foo/bar.txt', 'baz');
        $this->storage->assertDontSee('foo/bar.txt', 'kdkdkd');

        $this->storage->assertDirectoryNotEmpty('foo');
        $this->storage->assertDirectoryEmpty('empty');

        $storage->cleanDirectory('foo');
        $this->storage->assertDirectoryEmpty('foo');

        $storage->cleanDirectory();
        $this->storage->assertDirectoryEmpty();

        $storage->write('foo/bar.txt', 'baz');
        $storage->delete('foo/bar.txt');
        $this->storage->assertDirectoryEmpty('foo');

        $storage->deleteDirectory('foo');
        $this->storage->assertDirectoryEmpty();
    }

    public function test_public_url(): void
    {
        $this->storage->fake();
        $this->storage->createPublicUrlsUsing(fn (string $path) => sprintf('https://localhost/%s', $path));

        $storage = $this->container->get(Storage::class);
        $storage->write('foo.txt', 'bar');

        $this->assertSame('https://localhost/foo.txt', $storage->publicUrl('foo.txt'));
    }

    public function test_temporary_urls(): void
    {
        $this->storage->fake();
        $this->storage->createTemporaryUrlsUsing(fn (string $path, DateTimeInterface $expiresAt) => sprintf(
            'https://localhost/%s?expires=%s',
            $path,
            $expiresAt->format(DateTimeInterface::RFC3339),
        ));

        $storage = $this->container->get(Storage::class);
        $storage->write('bar.txt', 'baz');

        $url = $storage->temporaryUrl('bar.txt', DateTime::createFromFormat('Y-m-d', '2024-01-01')->setTime(0, 0));

        $this->assertEquals('https://localhost/bar.txt?expires=2024-01-01T00:00:00+00:00', $url);
    }

    public function test_prevent_usage_without_fake(): void
    {
        $this->expectException(ForbiddenStorageUsageException::class);

        $this->storage->preventUsageWithoutFake();

        $storage = $this->container->get(Storage::class);
        $storage->write('bar.txt', 'baz');
    }

    public function test_prevent_usage_without_fake_with_fake(): void
    {
        $this->storage->preventUsageWithoutFake();
        $this->storage->fake();

        $storage = $this->container->get(Storage::class);
        $storage->write('bar.txt', 'baz');

        $this->storage->assertFileExists('bar.txt');
    }

    public function test_no_adapter(): void
    {
        $this->expectException(MissingAdapterException::class);
        $this->expectExceptionMessage('The `UnknownClass` adapter is missing');

        $this->container->config(new class implements StorageConfig {
            public string $adapter = 'UnknownClass';

            public bool $readonly = false;

            public function createAdapter(): FilesystemAdapter
            {
                return new LocalFilesystemAdapter(__DIR__);
            }
        });

        $storage = $this->container->get(Storage::class);
        $storage->write('bar.txt', 'baz');
    }
}
