<?php

declare(strict_types=1);

namespace Tempest\Filesystem\Tests;

use const DIRECTORY_SEPARATOR;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tempest\Filesystem\ErrorContext;
use Tempest\Filesystem\Exceptions\FileDoesNotExist;
use Tempest\Filesystem\Exceptions\UnableToCopyFile;
use Tempest\Filesystem\Exceptions\UnableToCreateDirectory;
use Tempest\Filesystem\LocalFilesystem;

/**
 * @internal
 */
final class LocalFilesystemTest extends TestCase
{
    private string $sandbox = __DIR__ . '/Sandbox';

    protected function setUp(): void
    {
        parent::setUp();

        mkdir($this->sandbox);
    }

    protected function tearDown(): void
    {
        parent::setUp();

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->sandbox, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $file) {
            $todo = ($file->isDir() ? 'rmdir' : 'unlink');
            $todo($file->getRealPath());
        }

        rmdir($this->sandbox);
    }

    private function path(string $path): string
    {
        return $this->sandbox . DIRECTORY_SEPARATOR . ltrim($path, '/\\');
    }

    public function test_writing_files(): void
    {
        $filePath = $this->path('test.txt');

        (new LocalFilesystem())->write($filePath, 'Hello world!');

        $this->assertStringEqualsFile($filePath, 'Hello world!');
    }

    public function test_reading_files(): void
    {
        $filePath = $this->path('test.txt');

        file_put_contents($filePath, 'Hello world!');

        $text = (new LocalFilesystem())->read($filePath);

        $this->assertSame('Hello world!', $text);
    }

    public function test_deleting_files(): void
    {
        $filePath = $this->path('to-be-deleted.txt');

        file_put_contents($filePath, 'Hello world!');

        (new LocalFilesystem())->delete($filePath);

        $this->assertFileDoesNotExist($filePath);
    }

    public function test_checking_file_existence(): void
    {
        $filePath = $this->path('test.txt');

        $this->assertFalse(
            (new LocalFilesystem())->exists($filePath)
        );

        file_put_contents($filePath, 'Hello world!');

        $this->assertTrue(
            (new LocalFilesystem())->exists($filePath)
        );
    }

    public function test_copying_files(): void
    {
        $filePath1 = $this->path('test.txt');
        $filePath2 = $this->path('test2.txt');

        file_put_contents($filePath1, 'Hello world!');

        (new LocalFilesystem())->copy($filePath1, $filePath2);

        $this->assertFileEquals($filePath2, $filePath1);
    }

    public function test_exception_is_thrown_if_source_file_doesnt_exist_when_copying(): void
    {
        $filePath1 = $this->path('test.txt');
        $filePath2 = $this->path('test2.txt');

        $this->expectExceptionObject(
            FileDoesNotExist::atPath($filePath1)
        );

        (new LocalFilesystem())->copy($filePath1, $filePath2);
    }

    public function test_exception_is_thrown_if_there_is_an_error_copying(): void
    {
        $filePath1 = $this->path('test.txt');
        $filePath2 = $this->path('nested-dir/test2.txt');

        $this->expectExceptionObject(
            UnableToCopyFile::fromSourceToDestination($filePath1, $filePath2, new ErrorContext())
        );

        file_put_contents($filePath1, 'Hello world!');

        (new LocalFilesystem())->copy($filePath1, $filePath2);
    }

    public function test_moving_files(): void
    {
        $filePath1 = $this->path('test.txt');
        $filePath2 = $this->path('test2.txt');

        file_put_contents($filePath1, 'Hello world!');

        (new LocalFilesystem())->move($filePath1, $filePath2);

        $this->assertFileDoesNotExist($filePath1);
        $this->assertStringEqualsFile($filePath2, 'Hello world!');
    }

    public function test_making_a_directory(): void
    {
        $directoryPath = $this->path('test-directory');

        (new LocalFilesystem())->createDirectory($directoryPath);

        $this->assertDirectoryExists($directoryPath);
    }

    public function test_making_a_directory_recursively(): void
    {
        $directoryPath = $this->path('test-directory/nested-test-directory');

        (new LocalFilesystem())->createDirectory($directoryPath);

        $this->assertDirectoryExists($directoryPath);
    }

    public function test_making_a_directory_recursively_without_recursive_enabled_fails(): void
    {
        $directoryPath = $this->path('test-directory/nested-test-directory');

        // Update
        $this->expectException(UnableToCreateDirectory::class);

        (new LocalFilesystem())->createDirectory(
            path: $directoryPath,
            recursive: false
        );
    }

    public function test_is_directory(): void
    {
        $filesystem = new LocalFilesystem();
        $directory = $this->path('test-directory/nested-test-directory');

        $this->assertFalse($filesystem->isDirectory($directory));

        $filesystem->createDirectory($directory);

        $this->assertTrue($filesystem->isDirectory($directory));
    }
}
