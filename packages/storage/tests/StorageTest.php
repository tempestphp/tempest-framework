<?php

namespace Tempest\Storage\Tests;

use League\Flysystem\UnableToWriteFile;
use PHPUnit\Framework\Attributes\Test;
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

    #[Test]
    public function storage_write(): void
    {
        $storage = new GenericStorage(new LocalStorageConfig(
            path: $this->fixtures,
        ));

        $storage->write('foo.txt', 'bar');

        $this->assertTrue(Filesystem\is_file($this->fixtures . 'foo.txt'));
        $this->assertSame('bar', Filesystem\read_file($this->fixtures . 'foo.txt'));
    }

    #[Test]
    public function storage_read(): void
    {
        Filesystem\write_file($this->fixtures . 'foo.txt', 'baz');

        $storage = new GenericStorage(new LocalStorageConfig(
            path: $this->fixtures,
        ));

        $this->assertSame('baz', $storage->read('foo.txt'));
    }

    #[Test]
    public function storage_list(): void
    {
        Filesystem\write_file($this->fixtures . 'foo.txt', 'baz');

        $storage = new GenericStorage(new LocalStorageConfig(
            path: $this->fixtures,
        ));

        $this->assertCount(1, $storage->list()->toArray());
    }

    #[Test]
    public function storage_list_deep(): void
    {
        Filesystem\write_file($this->fixtures . 'foo.txt', 'baz');
        Filesystem\write_file($this->fixtures . 'dir/baz.txt', 'bar');

        $storage = new GenericStorage(new LocalStorageConfig(
            path: $this->fixtures,
        ));

        $this->assertCount(3, $storage->list(deep: true)->toArray());
        $this->assertCount(1, $storage->list(location: 'dir')->toArray());
    }

    #[Test]
    public function storage_clean_directory(): void
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

    #[Test]
    public function storage_readonly(): void
    {
        $this->expectException(UnableToWriteFile::class);

        $storage = new GenericStorage(new LocalStorageConfig(
            path: $this->fixtures,
            readonly: true,
        ));

        $storage->write('foo.txt', 'bar');
    }
}
