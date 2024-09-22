<?php

declare(strict_types=1);

namespace Tempest\Filesystem\Tests;

use const DIRECTORY_SEPARATOR;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;
use Tempest\Filesystem\LocalFilesystem;

/**
 * @internal
 */
final class LocalFilesystemTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::setUp();

        $recursiveDirectoryIterator = new RecursiveDirectoryIterator(__DIR__ . DIRECTORY_SEPARATOR . 'Fixtures', RecursiveDirectoryIterator::SKIP_DOTS);
        $recursiveIteratorIterator = new RecursiveIteratorIterator($recursiveDirectoryIterator, RecursiveIteratorIterator::SELF_FIRST);

        /**
         * @var SplFileInfo $file
         */
        foreach ($recursiveIteratorIterator as $file) {
            if (! $file->isFile()) {
                continue;
            }

            if (str_starts_with($file->getFilename(), '.')) {
                continue;
            }

            @unlink($file->getRealPath());
        }

        // Remove testing directories.
        @rmdir(__DIR__ . DIRECTORY_SEPARATOR . 'Fixtures' . DIRECTORY_SEPARATOR . 'test-directory'. DIRECTORY_SEPARATOR . 'nested-test-directory');
        @rmdir(__DIR__ . DIRECTORY_SEPARATOR . 'Fixtures' . DIRECTORY_SEPARATOR . 'test-directory');
    }

    public function test_writing_files(): void
    {
        (new LocalFilesystem())->write(__DIR__ . '/Fixtures/test.txt', 'Hello world!');

        $this->assertStringEqualsFile(__DIR__ . '/Fixtures/test.txt', 'Hello world!');
    }

    public function test_reading_files(): void
    {
        file_put_contents(__DIR__ . '/Fixtures/test.txt', 'Hello world!');

        $text = (new LocalFilesystem())->read(__DIR__ . '/Fixtures/test.txt');

        $this->assertSame('Hello world!', $text);
    }

    public function test_deleting_files(): void
    {
        file_put_contents(__DIR__ . '/Fixtures/to-be-deleted.txt', 'Hello world!');

        (new LocalFilesystem())->delete(__DIR__ . '/Fixtures/to-be-deleted.txt');

        $this->assertFileDoesNotExist(__DIR__ . '/Fixtures/to-be-deleted.txt');
    }

    public function test_checking_file_existence(): void
    {
        $this->assertFalse(
            (new LocalFilesystem())->exists(__DIR__ . '/Fixtures/test.txt')
        );

        file_put_contents(__DIR__ . '/Fixtures/test.txt', 'Hello world!');

        $this->assertTrue(
            (new LocalFilesystem())->exists(__DIR__ . '/Fixtures/test.txt')
        );
    }

    public function test_copying_files(): void
    {
        file_put_contents(__DIR__ . '/Fixtures/test.txt', 'Hello world!');

        (new LocalFilesystem())->copy(__DIR__ . '/Fixtures/test.txt', __DIR__ . '/Fixtures/test2.txt');

        $this->assertFileEquals(__DIR__ . '/Fixtures/test2.txt', __DIR__ . '/Fixtures/test.txt');
    }

    public function test_moving_files(): void
    {
        file_put_contents(__DIR__ . '/Fixtures/test.txt', 'Hello world!');

        (new LocalFilesystem())->move(__DIR__ . '/Fixtures/test.txt', __DIR__ . '/Fixtures/test2.txt');

        $this->assertFileDoesNotExist(__DIR__ . '/Fixtures/test.txt');
        $this->assertStringEqualsFile(__DIR__ . '/Fixtures/test2.txt', 'Hello world!');
    }

    public function test_making_a_directory(): void
    {
        (new LocalFilesystem())->makeDirectory(__DIR__ . '/Fixtures/test-directory');

        $this->assertDirectoryExists(__DIR__ . '/Fixtures/test-directory');
    }

    public function test_making_a_directory_recursively(): void
    {
        (new LocalFilesystem())->makeDirectory(__DIR__ . '/Fixtures/test-directory/nested-test-directory');

        $this->assertDirectoryExists(__DIR__ . '/Fixtures/test-directory/nested-test-directory');
    }

    public function test_making_a_directory_recursively_without_recursive_enabled_fails(): void
    {
        $this->expectExceptionObject(new RuntimeException());

        (new LocalFilesystem())->makeDirectory(
            path: __DIR__ . '/Fixtures/test-directory/nested-test-directory',
            recursive: false
        );
    }
}
