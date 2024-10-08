<?php

declare(strict_types=1);

namespace Tempest\Filesystem;

interface Filesystem
{
    public function read(string $filePath): string;

    public function write(string $filePath, string $content): void;

    public function delete(string $filePath): void;

    public function exists(string $filePath): bool;

    public function copy(string $sourcePath, string $destinationPath): void;

    public function move(string $sourcePath, string $destinationPath): void;

    public function createDirectory(string $path, int $permissions = Permission::FULL->value, bool $recursive = true): void;

    public function deleteDirectory(string $path, bool $recursive = false): void;

    public function isDirectory(string $path): bool;
}
