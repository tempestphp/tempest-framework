<?php

namespace Tempest\Storage\Tests;

use League\Flysystem\UnableToWriteFile;
use PHPUnit\Framework\TestCase;
use Tempest\Filesystem\LocalFilesystem;
use Tempest\Storage\Config\LocalStorageConfig;
use Tempest\Storage\GenericStorage;

final class StorageTest extends TestCase
{
    private string $fixtures = __DIR__ . '/Fixtures/';

    protected function tearDown(): void
    {
        parent::tearDown();

        $filesystem = new LocalFilesystem();
        $filesystem->deleteDirectory($this->fixtures);
    }

    public function test_storage_write(): void
    {
        $storage = new GenericStorage(new LocalStorageConfig(
            path: $this->fixtures,
        ));

        $storage->write('foo.txt', 'bar');

        $this->assertTrue(file_exists($this->fixtures . 'foo.txt'));
        $this->assertSame('bar', file_get_contents($this->fixtures . 'foo.txt'));
    }

    public function test_storage_read(): void
    {
        mkdir($this->fixtures);
        file_put_contents($this->fixtures . 'foo.txt', 'baz');

        $storage = new GenericStorage(new LocalStorageConfig(
            path: $this->fixtures,
        ));

        $this->assertSame('baz', $storage->read('foo.txt'));
    }

    public function test_storage_clean_directory(): void
    {
        mkdir($this->fixtures);
        file_put_contents($this->fixtures . 'foo.txt', 'baz');
        mkdir($this->fixtures . 'dir');
        file_put_contents($this->fixtures . 'dir/foo.txt', 'baz');

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
