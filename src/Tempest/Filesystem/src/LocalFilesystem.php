<?php

declare(strict_types=1);

namespace Tempest\Filesystem;

use const FILE_APPEND;
use FilesystemIterator;
use const LOCK_EX;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tempest\Filesystem\Exceptions\FileDoesNotExist;
use Tempest\Filesystem\Exceptions\UnableToCopyFile;
use Tempest\Filesystem\Exceptions\UnableToCreateDirectory;
use Tempest\Filesystem\Exceptions\UnableToDeleteDirectory;
use Tempest\Filesystem\Exceptions\UnableToDeleteFile;
use Tempest\Filesystem\Exceptions\UnableToReadFile;
use Tempest\Filesystem\Exceptions\UnableToWriteFile;

final class LocalFilesystem implements Filesystem
{
    public function read(string $filePath): string
    {
        $error = ErrorContext::reset();

        if (! $this->isFile($filePath)) {
            throw FileDoesNotExist::atPath($filePath);
        }

        $contents = @file_get_contents($filePath);

        if ($contents === false) {
            throw UnableToReadFile::atPath($filePath, $error);
        }

        return $contents;
    }

    public function write(string $filePath, string $content): void
    {
        // If the directory we are attempting to write the file to
        // doesn't exist, create it before writing the file.
        // TODO: Move this to a path helper.
        $directoryPath = dirname($filePath);

        if (! $this->isDirectory($directoryPath)) {
            // TODO: I'm not convinced this is best for permissions. Let's revisit.
            $this->createDirectory($directoryPath);
        }

        // Write the file.
        $error = ErrorContext::reset();
        $successfullyWrittenBytes = @file_put_contents($filePath, $content, LOCK_EX);

        if ($successfullyWrittenBytes === false) {
            throw UnableToWriteFile::atPath($filePath, $error);
        }
    }

    public function append(string $filePath, string $content): void
    {
        $error = ErrorContext::reset();
        $successfullyWrittenBytes = @file_put_contents($filePath, $content, LOCK_EX | FILE_APPEND);

        if ($successfullyWrittenBytes === false) {
            throw UnableToWriteFile::atPath($filePath, $error);
        }
    }

    public function deleteFile(string $filePath): void
    {
        if (@unlink($filePath) === false) {
            throw UnableToDeleteFile::atPath($filePath);
        }
    }

    public function isFile(string $path): bool
    {
        return is_file($path);
    }

    public function createDirectory(string $directoryPath, int $permissions = Permission::FULL->value, bool $recursive = true): void
    {
        $error = ErrorContext::reset();

        if (@mkdir($directoryPath, $permissions, $recursive) === false) {
            throw UnableToCreateDirectory::atPath($directoryPath, $error->commit());
        }
    }

    public function ensureDirectoryExists(string $directoryPath, int $permissions = Permission::FULL->value): void
    {
        if (! $this->isDirectory($directoryPath)) {
            $this->createDirectory($directoryPath, $permissions, true);
        }

        // TODO: We are not checking for the existence post-creation. Do we care or do we trust PHP's return?
    }

    public function deleteDirectory(string $directoryPath, bool $recursive = true): void
    {
        if (! $this->isDirectory($directoryPath)) {
            return;
        }

        // If we're not recursively deleting the directory, simply
        // attempt to remove it without checking for children
        // and throw an exception on any errors.
        if ($recursive === false) {
            $error = ErrorContext::reset();
            $successfullyDeleted = @rmdir($directoryPath);

            if ($successfullyDeleted === false) {
                throw UnableToDeleteDirectory::atPath($directoryPath, $error->commit());
            }

            return;
        }

        // Iterate through the directory contents and
        // use helpers to delete the child items.
        $recursiveDirectoryIterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directoryPath, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($recursiveDirectoryIterator as $item) {
            $item->isDir()
                ? $this->deleteDirectory($item->getPathname())
                : $this->deleteFile($item->getPathname());
        }

        // Wrap up a recursive delete by deleting the parent.
        $this->deleteDirectory($directoryPath, false);
    }

    public function isDirectory(string $path): bool
    {
        return is_dir($path);
    }

    public function exists(string $path): bool
    {
        return file_exists($path);
    }

    public function copy(string $sourcePath, string $destinationPath): void
    {
        $error = ErrorContext::reset();

        if (! $this->exists($sourcePath)) {
            throw FileDoesNotExist::atPath($sourcePath);
        }

        if (@copy($sourcePath, $destinationPath) === false) {
            throw UnableToCopyFile::fromSourceToDestination($sourcePath, $destinationPath, $error);
        }
    }

    public function move(string $sourcePath, string $destinationPath): void
    {
        $this->copy($sourcePath, $destinationPath);
        $this->delete($sourcePath);
    }

    public function delete(string $path): void
    {
        $this->isFile($path)
            ? $this->deleteFile($path)
            : $this->deleteDirectory($path);
    }
}
