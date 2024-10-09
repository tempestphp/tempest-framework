<?php

declare(strict_types=1);

namespace Tempest\Filesystem\Tests;

use bovigo\vfs\vfsStream;
use const DIRECTORY_SEPARATOR;
use const PHP_EOL;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tempest\Filesystem\ErrorContext;
use Tempest\Filesystem\Exceptions\FileDoesNotExist;
use Tempest\Filesystem\Exceptions\UnableToCopyFile;
use Tempest\Filesystem\Exceptions\UnableToCreateDirectory;
use Tempest\Filesystem\Exceptions\UnableToDeleteFile;
use Tempest\Filesystem\Exceptions\UnableToReadFile;
use Tempest\Filesystem\Exceptions\UnableToWriteFile;
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

    public function test_reading_files(): void
    {
        $filePath = $this->path('test.txt');

        file_put_contents($filePath, 'Hello world!');

        $text = (new LocalFilesystem())->read($filePath);

        $this->assertSame('Hello world!', $text);
    }

    public function test_exception_is_thrown_when_reading_a_file_that_doesnt_exist(): void
    {
        $filePath = $this->path('test.txt');

        $this->expectExceptionObject(
            FileDoesNotExist::atPath($filePath),
        );

        (new LocalFilesystem())->read($filePath);
    }

    public function test_exception_is_thrown_when_there_is_an_error_reading_a_file(): void
    {
        $root = vfsStream::setup('root', null, [
            'file.txt' => 'content',
        ]);

        $filePath = vfsStream::url('root/file.txt');

        $this->expectExceptionObject(
            UnableToReadFile::atPath($filePath, new ErrorContext())
        );

        // Make the file unreadable.
        $root->getChild('file.txt')->chmod(0000);

        (new LocalFilesystem())->read($filePath);
    }

    public function test_writing_files(): void
    {
        $filePath = $this->path('test.txt');

        (new LocalFilesystem())->write($filePath, 'Hello world!');

        $this->assertStringEqualsFile($filePath, 'Hello world!');
    }

    public function test_exception_is_thrown_when_there_is_an_error_writing_a_file(): void
    {
        vfsStream::setup('root', 0000);

        $filePath = vfsStream::url('root/file.txt');

        $this->expectExceptionObject(
            UnableToWriteFile::atPath($filePath, new ErrorContext())
        );

        (new LocalFilesystem())->write($filePath, 'Hello world!');
    }

    public function test_appending_to_a_file(): void
    {
        vfsStream::setup('root', null, [
            'file.txt' => 'Line 1' . PHP_EOL,
        ]);

        $filePath = vfsStream::url('root/file.txt');

        (new LocalFilesystem())->append($filePath, 'Line 2' . PHP_EOL);

        $this->assertStringEqualsFile($filePath, 'Line 1' . PHP_EOL . 'Line 2' . PHP_EOL);
    }

    public function test_exception_is_thrown_when_there_is_an_error_appending_to_a_file(): void
    {
        $root = vfsStream::setup('root', null, [
            'file.txt' => 'Line 1' . PHP_EOL,
        ]);

        $root->getChild('file.txt')->chmod(0000);

        $filePath = vfsStream::url('root/file.txt');

        $this->expectExceptionObject(
            UnableToWriteFile::atPath($filePath, new ErrorContext())
        );

        (new LocalFilesystem())->append($filePath, 'Line 2' . PHP_EOL);
    }

    public function test_deleting_files(): void
    {
        $filePath = $this->path('to-be-deleted.txt');

        file_put_contents($filePath, 'Hello world!');

        (new LocalFilesystem())->delete($filePath);

        $this->assertFileDoesNotExist($filePath);
    }

    public function test_exception_is_thrown_when_there_is_an_error_deleting_a_file(): void
    {
        vfsStream::setup('root', 0000, [
            'file.txt' => 'Hello world!',
        ]);

        $filePath = vfsStream::url('root/file.txt');

        $this->expectExceptionObject(
            UnableToDeleteFile::atPath($filePath)
        );

        (new LocalFilesystem())->delete($filePath);
    }

    public function test_is_file(): void
    {
        $filesystem = new LocalFilesystem();
        $directoryPath = $this->path('some-directory');
        $filePath = $this->path('some-file.txt');

        mkdir($directoryPath);
        file_put_contents($filePath, 'Hello world!');

        $this->assertFalse($filesystem->isFile($directoryPath));
        $this->assertTrue($filesystem->isFile($filePath));
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
