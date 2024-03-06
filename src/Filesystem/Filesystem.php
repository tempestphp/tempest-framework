<?php

declare(strict_types=1);

namespace Tempest\Filesystem;

use Tempest\Filesystem\Driver\LocalFilesystemDriver;

final readonly class Filesystem
{
    public function __construct(private LocalFilesystemDriver $driver)
    {
    }

    public function isFile(string $path): bool
    {
        return $this->driver->isFile($path);
    }

    public function isDirectory(string $path): bool
    {
        return $this->driver->isDirectory($path);
    }

    public function createDirectory(string $path, int $mode = 0777): void
    {
        $this->driver->createDirectory($path, $mode);
    }

    public function deleteDirectory(string $path): void
    {
        $this->driver->deleteDirectory($path);
    }
}
