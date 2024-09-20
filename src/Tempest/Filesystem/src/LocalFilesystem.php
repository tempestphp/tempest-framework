<?php

declare(strict_types=1);

namespace Tempest\Filesystem;

use Tempest\Filesystem\Exceptions\FileDoesNotExist;
use Tempest\Filesystem\Exceptions\UnableToCopyFile;
use Tempest\Filesystem\Exceptions\UnableToDeleteFile;

final class LocalFilesystem implements Filesystem
{
    public function read(string $filePath): string
    {
        if (! $this->exists($filePath)) {
            throw FileDoesNotExist::atPath($filePath);
        }

        return file_get_contents($filePath);
    }

    public function write(string $filePath, string $content): void
    {
        file_put_contents($filePath, $content, LOCK_EX);
    }

    public function delete(string $filePath): void
    {
        if (unlink($filePath) === false) {
            throw UnableToDeleteFile::atPath($filePath);
        }
    }

    public function exists(string $filePath): bool
    {
        return file_exists($filePath);
    }

    public function copy(string $sourcePath, string $destinationPath): void
    {
        if (! $this->exists($sourcePath)) {
            throw FileDoesNotExist::atPath($sourcePath);
        }

        if (copy($sourcePath, $destinationPath) === false) {
            throw UnableToCopyFile::fromSourceToDestination($sourcePath, $destinationPath);
        }
    }

    public function move(string $sourcePath, string $destinationPath): void
    {
        $this->copy($sourcePath, $destinationPath);
        $this->delete($sourcePath);
    }
}
