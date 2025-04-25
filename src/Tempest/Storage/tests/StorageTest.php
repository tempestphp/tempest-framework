<?php

namespace Tempest\Storage\Tests;

use League\Flysystem\UnableToWriteFile;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tempest\Storage\Config\LocalStorageConfig;
use Tempest\Storage\GenericStorage;

#[CoversClass(GenericStorage::class)]
final class StorageTest extends TestCase
{
    private string $fixtures = __DIR__ . '/Fixtures/';

    protected function tearDown(): void
    {
        parent::tearDown();

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->fixtures, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($files as $file) {
            $file->isDir()
                ? @rmdir($file->getRealPath())
                : @unlink($file->getRealPath());
        }

        @rmdir($this->fixtures);
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

    public function test_storage_list(): void
    {
        mkdir($this->fixtures);
        file_put_contents($this->fixtures . 'foo.txt', 'baz');

        $storage = new GenericStorage(new LocalStorageConfig(
            path: $this->fixtures,
        ));

        $this->assertCount(1, $storage->list()->toArray());
    }

    public function test_storage_list_deep(): void
    {
        mkdir($this->fixtures);
        file_put_contents($this->fixtures . 'foo.txt', 'baz');
        mkdir($this->fixtures . 'dir');
        file_put_contents($this->fixtures . 'dir/baz.txt', 'bar');

        $storage = new GenericStorage(new LocalStorageConfig(
            path: $this->fixtures,
        ));

        $this->assertCount(3, $storage->list(deep: true)->toArray());
        $this->assertCount(1, $storage->list(location: 'dir')->toArray());
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
