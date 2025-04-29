<?php

namespace Tests\Tempest\Integration\Storage;

use DateTime;
use DateTimeInterface;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Tempest\Storage\Config\InMemoryStorageConfig;
use Tempest\Storage\Config\StorageConfig;
use Tempest\Storage\ForbiddenStorageUsageException;
use Tempest\Storage\MissingAdapterException;
use Tempest\Storage\Storage;
use Tempest\Storage\Testing\TestingStorage;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use UnitEnum;

final class StorageTesterTest extends FrameworkIntegrationTestCase
{
    public function test_fake_storage_is_registered_in_container(): void
    {
        $faked = $this->storage->fake();
        $actual = $this->container->get(Storage::class);

        $this->assertInstanceOf(TestingStorage::class, $faked);
        $this->assertInstanceOf(TestingStorage::class, $actual);
        $this->assertSame($faked, $actual);
    }

    public function test_multiple_fake_storage_are_registered_in_container(): void
    {
        $faked1 = $this->storage->fake('storage1');
        $faked2 = $this->storage->fake('storage2');

        $actual1 = $this->container->get(Storage::class, 'storage1');
        $actual2 = $this->container->get(Storage::class, 'storage2');

        $this->assertInstanceOf(TestingStorage::class, $faked1);
        $this->assertInstanceOf(TestingStorage::class, $actual1);
        $this->assertSame($faked1, $actual1);

        $this->assertInstanceOf(TestingStorage::class, $faked2);
        $this->assertInstanceOf(TestingStorage::class, $actual2);
        $this->assertSame($faked2, $actual2);

        $this->assertNotSame($actual1, $actual2);
    }

    public function test_multiple_storages(): void
    {
        $storage1 = $this->storage->fake('storage1');
        $storage2 = $this->storage->fake('storage2');

        $storage1->write('foo1.txt', 'bar');
        $storage2->write('foo2.txt', 'bar');

        $storage1->assertFileExists('foo1.txt');
        $storage1->assertSee('foo1.txt', 'bar');
        $storage1->assertFileDoesNotExist('foo2.txt');

        $storage2->assertFileExists('foo2.txt');
        $storage2->assertSee('foo2.txt', 'bar');
        $storage2->assertFileDoesNotExist('foo1.txt');
    }

    public function test_basic(): void
    {
        $storage = $this->storage->fake();

        $storage->write('foo.txt', 'bar');

        $storage->assertFileExists('foo.txt');
        $storage->assertSee('foo.txt', 'bar');
    }

    public function test_file_assertions(): void
    {
        $storage = $this->storage->fake();

        $storage->write('foo.txt', 'bar');

        $storage->assertFileExists('foo.txt');
        $storage->assertFileOrDirectoryExists('foo.txt');
        $storage->assertDontSee('foo.txt', 'kdkdkd');

        $storage->assertFileDoesNotExist('do-not-exists.txt');
        $storage->assertFileOrDirectoryDoesNotExist('do-not-exists.txt');
    }

    public function test_directory_assertions(): void
    {
        $storage = $this->storage->fake();

        $storage->write('foo/bar.txt', 'baz');
        $storage->createDirectory('empty');

        $storage->assertChecksumEquals('foo/bar.txt', '73feffa4b7f6bb68e44cf984c85f6e88');

        $storage->assertFileExists('foo/bar.txt');
        $storage->assertFileOrDirectoryExists('foo/bar.txt');
        $storage->assertDirectoryExists('foo');
        $storage->assertFileOrDirectoryExists('foo');
        $storage->assertDirectoryNotEmpty('foo');

        $storage->assertSee('foo/bar.txt', 'baz');
        $storage->assertDontSee('foo/bar.txt', 'kdkdkd');

        $storage->assertDirectoryNotEmpty('foo');
        $storage->assertDirectoryEmpty('empty');

        $storage->cleanDirectory('foo');
        $storage->assertDirectoryEmpty('foo');

        $storage->cleanDirectory();
        $storage->assertDirectoryEmpty();

        $storage->write('foo/bar.txt', 'baz');
        $storage->delete('foo/bar.txt');
        $storage->assertDirectoryEmpty('foo');

        $storage->deleteDirectory('foo');
        $storage->assertDirectoryEmpty();
    }

    public function test_public_url(): void
    {
        $storage = $this->storage->fake();

        $storage->createPublicUrlsUsing(fn (string $path) => sprintf('https://localhost/%s', $path));

        $storage = $this->container->get(Storage::class);
        $storage->write('foo.txt', 'bar');

        $this->assertSame('https://localhost/foo.txt', $storage->publicUrl('foo.txt'));
    }

    public function test_temporary_urls(): void
    {
        $storage = $this->storage->fake();

        $storage->createTemporaryUrlsUsing(fn (string $path, DateTimeInterface $expiresAt) => sprintf(
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

    public function test_prevent_usage_without_fake_with_tagged_storage(): void
    {
        $this->expectException(ForbiddenStorageUsageException::class);

        $this->container->config(new InMemoryStorageConfig(tag: 'tagged'));
        $this->storage->preventUsageWithoutFake();

        $storage = $this->container->get(Storage::class, 'tagged');
        $storage->write('bar.txt', 'baz');
    }

    public function test_prevent_usage_without_fake_with_fake(): void
    {
        $this->storage->preventUsageWithoutFake();

        $storage = $this->storage->fake();
        $storage->write('bar.txt', 'baz');
        $storage->assertFileExists('bar.txt');
    }

    public function test_prevent_usage_without_fake_with_fake_tagged_storage(): void
    {
        $this->container->config(new InMemoryStorageConfig(tag: 'tagged'));
        $this->storage->preventUsageWithoutFake();

        $storage = $this->storage->fake('tagged');
        $storage->write('bar.txt', 'baz');
        $storage->assertFileExists('bar.txt');
    }

    public function test_no_adapter(): void
    {
        $this->expectException(MissingAdapterException::class);
        $this->expectExceptionMessage('The `UnknownClass` adapter is missing');

        $this->container->config(new class implements StorageConfig {
            public string $adapter = 'UnknownClass';

            public null|string|UnitEnum $tag = null;

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
