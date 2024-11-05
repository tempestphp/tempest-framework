<?php

declare(strict_types=1);

namespace Tempest\Filesystem;

use Tempest\Filesystem\Exceptions\FileDoesNotExist;
use Tempest\Filesystem\Exceptions\UnableToCreateDirectory;
use Tempest\Filesystem\Exceptions\UnableToDeleteDirectory;
use Tempest\Filesystem\Exceptions\UnableToReadFile;
use Tempest\Filesystem\Exceptions\UnableToWriteFile;

interface Filesystem
{
    /**
     * Reads the file at the specified path.
     *
     * @throws FileDoesNotExist if the file is not found.
     * @throws UnableToReadFile if there is an issue reading the file.
     */
    public function read(string $filePath): string;

    /**
     * Writes to the file at the specified path.
     *
     * @throws UnableToWriteFile if there is an issue writing the file.
     */
    public function write(string $filePath, string $content): void;

    /**
     * Append content to the file at the specified path.
     *
     * @throws UnableToWriteFile if there is an issue appending to the file.
     */
    public function append(string $filePath, string $content): void;

    /**
     * Determines if the specified path is a file.
     * Returns `false` if the path is a directory.
     */
    public function isFile(string $path): bool;

    /**
     * Creates a directory at the specified path.
     *
     * @throws UnableToCreateDirectory if there is an issue creating the directory.
     */
    public function createDirectory(string $directoryPath, int $permissions = Permission::FULL->value, bool $recursive = true): void;

    /**
     * Checks if the specified directory path exists and creates it if not.
     *
     * @throws UnableToCreateDirectory if the directory does not exist and there is an issue creating it.
     */
    public function ensureDirectoryExists(string $directoryPath, int $permissions = Permission::FULL->value): void;

    /**
     * Deletes a directory at the specified path.
     *
     * @throws UnableToDeleteDirectory if there is an issue deleting the directory.
     */
    public function deleteDirectory(string $directoryPath): void;

    /**
     * Determines if the specified path is a directory.
     * Returns `false` if the path is a file.
     */
    public function isDirectory(string $path): bool;

    /**
     * Deletes the resource at the specified path,
     * regardless of if it is a file or a directory.
     */
    public function delete(string $path): void;

    /**
     * Determines if the specified path exists,
     * regardless of if it is a file or a directory.
     */
    public function exists(string $path): bool;

    /**
     * Copy the specified source file or directory to the destination path.
     */
    public function copy(string $sourcePath, string $destinationPath): void;

    /**
     * Move the specified source file or directory to the destination path.
     */
    public function move(string $sourcePath, string $destinationPath): void;
}
