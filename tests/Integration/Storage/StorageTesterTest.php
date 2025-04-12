<?php

namespace Tests\Tempest\Integration\Storage;

use DateTime;
use DateTimeInterface;
use Tempest\Storage\ForbiddenStorageUsageException;
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
}
