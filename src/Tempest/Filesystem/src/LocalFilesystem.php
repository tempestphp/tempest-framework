<?php

declare(strict_types=1);

namespace Tempest\Filesystem;

use const FILE_APPEND;
use const LOCK_EX;
use Tempest\Filesystem\Exceptions\FileDoesNotExist;
use Tempest\Filesystem\Exceptions\UnableToCopyFile;
use Tempest\Filesystem\Exceptions\UnableToCreateDirectory;
use Tempest\Filesystem\Exceptions\UnableToDeleteFile;
use Tempest\Filesystem\Exceptions\UnableToReadFile;
use Tempest\Filesystem\Exceptions\UnableToWriteFile;

final class LocalFilesystem implements Filesystem
{
    public function read(string $filePath): string
    {
        $error = ErrorContext::reset();

        if (! $this->exists($filePath)) {
            throw FileDoesNotExist::atPath($filePath);
        }

        $contents = file_get_contents($filePath);

        if ($contents === false) {
            throw UnableToReadFile::atPath($filePath, $error);
        }

        return $contents;
    }

    public function write(string $filePath, string $content): void
    {
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

    public function delete(string $filePath): void
    {
        if (@unlink($filePath) === false) {
            throw UnableToDeleteFile::atPath($filePath);
        }
    }

    public function exists(string $filePath): bool
    {
        return file_exists($filePath);
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

    public function createDirectory(string $path, int $permissions = Permission::FULL->value, bool $recursive = true): void
    {
        $error = ErrorContext::reset();

        if (@mkdir($path, $permissions, $recursive) === false) {
            throw UnableToCreateDirectory::atPath($path, $error->commit());
        }
    }

    public function deleteDirectory(string $path, bool $recursive = false): void
    {
        // TODO: Implement deleteDirectory() method.
    }

    public function isDirectory(string $path): bool
    {
        return is_dir($path);
    }
}
