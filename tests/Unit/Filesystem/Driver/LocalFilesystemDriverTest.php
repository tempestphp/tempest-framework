<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Filesystem\Driver;

use const DIRECTORY_SEPARATOR;
use FilesystemIterator;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Tempest\Filesystem\Driver\LocalFilesystemDriver;

/**
 * @internal
 * @small
 */
class LocalFilesystemDriverTest extends TestCase
{
    private LocalFilesystemDriver $driver;

    private string $path;

    public function test_is_file(): void
    {
        $this->assertFalse(
            $this->driver->isFile($this->path('this-file-does-not-exist.txt'))
        );

        $this->assertTrue(
            $this->driver->isFile($this->path('test-file.txt'))
        );
    }

    public function test_is_directory(): void
    {
        $this->assertFalse(
            $this->driver->isDirectory($this->path('this-directory-does-not-exist'))
        );

        mkdir($this->path('this-exists'));

        $this->assertTrue(
            $this->driver->isDirectory($this->path('this-exists'))
        );
    }

    public function test_creating_and_removing_a_directory(): void
    {
        $path = $this->path('test-dir');

        $this->driver->createDirectory($path, 0755);

        $this->assertTrue(
            $this->driver->isDirectory($path)
        );

        // TODO: Clean this up.
        $permissions = fileperms($path) & 0777;

        $this->assertSame(0755, $permissions);

        $this->driver->deleteDirectory($path);

        $this->assertFalse(
            $this->driver->isDirectory($path)
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->driver = new LocalFilesystemDriver();

        $this->path = __DIR__ . '/Playground';

        mkdir($this->path, 0777);

        file_put_contents($this->path . '/test-file.txt', 'This file is just a test.');
    }

    protected function tearDown(): void
    {
        /**
         * @var SplFileInfo[] $files
         */
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->path, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }

        rmdir($this->path);

        parent::tearDown();
    }

    private function path(string ...$parts): string
    {
        return $this->path . DIRECTORY_SEPARATOR . join(DIRECTORY_SEPARATOR, $parts);
    }
}
