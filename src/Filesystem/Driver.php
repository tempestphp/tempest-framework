<?php

declare(strict_types=1);

namespace Tempest\Filesystem;

use Tempest\Filesystem\Exception\UnableToCreateDirectory;
use Tempest\Filesystem\Exception\UnableToCreateStream;
use Tempest\Filesystem\Exception\UnableToDeleteDirectory;
use Tempest\Filesystem\Exception\UnableToReadFile;
use Tempest\Filesystem\Exception\UnableToWriteFile;

interface Driver
{
    /**
     * Reads a file at the specified path.
     *
     * @throws UnableToReadFile
     */
    public function read(string $location): string;

    /**
     * Writes a file at the specified path.
     *
     * @throws UnableToWriteFile
     */
    public function write(string $location, string $content): void;

    /**
     * Returns true if the specified location exists and is a file.
     */
    public function isFile(string $location): bool;

    /**
     * Returns true if the specified location exists and is a directory.
     */
    public function isDirectory(string $location): bool;

    /**
     * Creates a directory at the specified path.
     *
     * @throws UnableToCreateDirectory
     */
    public function createDirectory(string $location): void;

    /**
     * Deletes a directory at the specified path.
     *
     * @throws UnableToDeleteDirectory
     */
    public function deleteDirectory(string $location): void;

    /**
     * Creates a stream object that can be utilized to read a file.
     *
     * @throws UnableToCreateStream
     */
    public function createStream(string $location): Stream;
}
