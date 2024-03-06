<?php

declare(strict_types=1);

namespace Tempest\Filesystem\Driver;

use Tempest\Filesystem\FilesystemDriver;

class LocalFilesystemDriver implements FilesystemDriver
{
    public function isFile(string $path): bool
    {
        return is_file($path);
    }

    public function isDirectory(string $path): bool
    {
        return is_dir($path);
    }

    public function createDirectory(string $path, int $mode): void
    {
        // TODO: Error handling.
        mkdir($path, $mode, true);
        clearstatcache(false, $path);
    }

    public function deleteDirectory(string $path): void
    {
        // TODO: Error handling; recursive?
        rmdir($path);
    }
}
