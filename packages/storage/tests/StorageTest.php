<?php

namespace Tempest\Storage\Tests;

use League\Flysystem\UnableToWriteFile;
use PHPUnit\Framework\TestCase;
use Tempest\Storage\Config\LocalStorageConfig;
use Tempest\Storage\GenericStorage;
use Tempest\Support\Filesystem;

final class StorageTest extends TestCase
{
    private string $fixtures = __DIR__ . '/Fixtures/';

    protected function tearDown(): void
    {
        parent::tearDown();

        Filesystem\delete_directory($this->fixtures);
    }

    public function test_storage_write(): void
    {
        $storage = new GenericStorage(new LocalStorageConfig(
            path: $this->fixtures,
        ));

        $storage->write('foo.txt', 'bar');

        $this->assertTrue(Filesystem\is_file($this->fixtures . 'foo.txt'));
        $this->assertSame('bar', Filesystem\read_file($this->fixtures . 'foo.txt'));
    }

    public function test_storage_read(): void
    {
        Filesystem\write_file($this->fixtures . 'foo.txt', 'baz');

        $storage = new GenericStorage(new LocalStorageConfig(
            path: $this->fixtures,
        ));

        $this->assertSame('baz', $storage->read('foo.txt'));
    }

    public function test_storage_list(): void
    {
        Filesystem\write_file($this->fixtures . 'foo.txt', 'baz');

        $storage = new GenericStorage(new LocalStorageConfig(
            path: $this->fixtures,
        ));

        $this->assertCount(1, $storage->list()->toArray());
    }

    public function test_storage_list_deep(): void
    {
        Filesystem\write_file($this->fixtures . 'foo.txt', 'baz');
        Filesystem\write_file($this->fixtures . 'dir/baz.txt', 'bar');

        $storage = new GenericStorage(new LocalStorageConfig(
            path: $this->fixtures,
        ));

        $this->assertCount(3, $storage->list(deep: true)->toArray());
        $this->assertCount(1, $storage->list(location: 'dir')->toArray());
    }

    public function test_storage_clean_directory(): void
    {
        Filesystem\write_file($this->fixtures . 'foo.txt', 'baz');
        Filesystem\write_file($this->fixtures . 'dir/foo.txt', 'baz');

        $storage = new GenericStorage(new LocalStorageConfig(
            path: $this->fixtures,
        ));

        $storage->cleanDirectory('dir');
        $this->assertEmpty(glob($this->fixtures . 'dir/*'));

        $storage->cleanDirectory();
        $this->assertEmpty(glob($this->fixtures . '*'));
    }

    public function test_storage_readonly(): void
    {
        $this->expectException(UnableToWriteFile::class);

        $storage = new GenericStorage(new LocalStorageConfig(
            path: $this->fixtures,
            readonly: true,
        ));

        $storage->write('foo.txt', 'bar');
    }
}
