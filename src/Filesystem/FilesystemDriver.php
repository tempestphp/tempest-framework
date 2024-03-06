<?php

declare(strict_types=1);

namespace Tempest\Filesystem;

interface FilesystemDriver
{
    public function isFile(string $path): bool;

    public function isDirectory(string $path): bool;

    public function createDirectory(string $path, int $mode): void;

    public function deleteDirectory(string $path): void;
}
