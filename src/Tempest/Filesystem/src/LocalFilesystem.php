<?php

declare(strict_types=1);

namespace Tempest\Filesystem;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;
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
        if (! $this->exists($sourcePath)) {
            throw FileDoesNotExist::atPath($sourcePath);
        }

        if (@copy($sourcePath, $destinationPath) === false) {
            throw UnableToCopyFile::fromSourceToDestination($sourcePath, $destinationPath);
        }
    }

    public function move(string $sourcePath, string $destinationPath): void
    {
        $this->copy($sourcePath, $destinationPath);
        $this->delete($sourcePath);
    }

    public function makeDirectory(string $path, int $permissions = 0777, bool $recursive = true): void
    {
        if (@mkdir($path, $permissions, $recursive) === false) {
            // TODO: Update exception
            throw new RuntimeException();
        }
    }

    public function removeDirectory(string $path, bool $recursive = true): void
    {
        if ($recursive) {
            $this->traverseDirectory($path, function (SplFileInfo $file): void {
                if ($file->isDir()) {
                    $this->removeDirectory($file->getPathname());

                    return;
                }

                $this->delete($file->getPathname());
            });
        }

        if (@rmdir($path) === false) {
            // TODO: Update exception
            throw new RuntimeException();
        }
    }

    public function traverseDirectory(string $path, ?callable $callable = null, bool $recursive = true): void
    {
        $iterator = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);

        if ($recursive) {
            $iterator = new RecursiveIteratorIterator($iterator);
        }

        foreach ($iterator as $file) {
            $callable($file);
        }
    }
}
