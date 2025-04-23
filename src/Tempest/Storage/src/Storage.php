<?php

namespace Tempest\Storage;

use DateTimeInterface;

interface Storage
{
    /**
     * Writes the given `$contents` to the specified `$location`.
     */
    public function write(string $location, string $contents): static;

    /**
     * Writes the given `$contents` to the specified `$location`.
     */
    public function writeStream(string $location, mixed $contents): static;

    /**
     * Writes the contents of the file at the specified `$location`.
     */
    public function read(string $location): string;

    /**
     * Returns the contents of the file at the specified `$location` as a stream.
     */
    public function readStream(string $location): mixed;

    /**
     * Determines whether a file exists at the specified `$location`.
     */
    public function fileExists(string $location): bool;

    /**
     * Determines whether a directory exists at the specified `$location`.
     */
    public function directoryExists(string $location): bool;

    /**
     * Determines whether a file or a directory exists at the specified `$location`.
     */
    public function fileOrDirectoryExists(string $location): bool;

    /**
     * Deletes the file or directory at the specified `$location`.
     */
    public function delete(string $location): static;

    /**
     * Deletes the directory at the specified `$location`.
     */
    public function deleteDirectory(?string $location = ''): static;

    /**
     * Creates a directory at the specified `$location`.
     */
    public function createDirectory(?string $location = ''): static;

    /**
     * Cleans the directory at the specified `$location`.
     */
    public function cleanDirectory(?string $location = ''): static;

    /**
     * Moves the file or directory from `$source` to `$destination`.
     */
    public function move(string $source, string $destination): static;

    /**
     * Copies the file or directory from `$source` to `$destination`.
     */
    public function copy(string $source, string $destination): static;

    /**
     * Returns the size of the file at the specified `$location`.
     */
    public function fileSize(string $location): int;

    /**
     * Returns the last modified time of the file at the specified `$location`.
     */
    public function lastModified(string $location): int;

    /**
     * Returns the mime type of the file at the specified `$location`.
     */
    public function mimeType(string $location): string;

    /**
     * Sets the visibility of the file at the specified `$location`.
     */
    public function setVisibility(string $location, string $visibility): static;

    /**
     * Returns the visibility of the file at the specified `$location`.
     */
    public function visibility(string $location): string;

    /**
     * Gets a public URL to the file at the specified `$location`.
     */
    public function publicUrl(string $location): string;

    /**
     * Gets a temporary URL to the file at the specified `$location` that expires at the specified `$expiresAt` time.
     */
    public function temporaryUrl(string $location, DateTimeInterface $expiresAt): string;

    /**
     * Gets a checksum of the file at the specified `$location`.
     */
    public function checksum(string $location): string;

    /**
     * Lists files in the directory at the specified `$location`.
     */
    public function list(string $location = '', bool $deep = false): DirectoryListing;
}
