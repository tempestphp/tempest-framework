<?php

namespace Tempest\Storage\Testing;

use DateTimeInterface;
use Tempest\Storage\DirectoryListing;
use Tempest\Storage\ForbiddenStorageUsageException;
use Tempest\Storage\Storage;

final class RestrictedStorage implements Storage
{
    public function __construct(
        private readonly ?string $tag = null,
    ) {}

    public function write(string $location, string $contents): static
    {
        throw new ForbiddenStorageUsageException($this->tag);
    }

    public function writeStream(string $location, mixed $contents): static
    {
        throw new ForbiddenStorageUsageException($this->tag);
    }

    public function read(string $location): string
    {
        throw new ForbiddenStorageUsageException($this->tag);
    }

    public function readStream(string $location): mixed
    {
        throw new ForbiddenStorageUsageException($this->tag);
    }

    public function fileExists(string $location): bool
    {
        throw new ForbiddenStorageUsageException($this->tag);
    }

    public function directoryExists(string $location): bool
    {
        throw new ForbiddenStorageUsageException($this->tag);
    }

    public function fileOrDirectoryExists(string $location): bool
    {
        throw new ForbiddenStorageUsageException($this->tag);
    }

    public function delete(string $location): static
    {
        throw new ForbiddenStorageUsageException($this->tag);
    }

    public function deleteDirectory(?string $location = ''): static
    {
        throw new ForbiddenStorageUsageException($this->tag);
    }

    public function createDirectory(?string $location = ''): static
    {
        throw new ForbiddenStorageUsageException($this->tag);
    }

    public function cleanDirectory(?string $location = ''): static
    {
        throw new ForbiddenStorageUsageException($this->tag);
    }

    public function move(string $source, string $destination): static
    {
        throw new ForbiddenStorageUsageException($this->tag);
    }

    public function copy(string $source, string $destination): static
    {
        throw new ForbiddenStorageUsageException($this->tag);
    }

    public function fileSize(string $location): int
    {
        throw new ForbiddenStorageUsageException($this->tag);
    }

    public function lastModified(string $location): int
    {
        throw new ForbiddenStorageUsageException($this->tag);
    }

    public function mimeType(string $location): string
    {
        throw new ForbiddenStorageUsageException($this->tag);
    }

    public function setVisibility(string $location, string $visibility): static
    {
        throw new ForbiddenStorageUsageException($this->tag);
    }

    public function visibility(string $location): string
    {
        throw new ForbiddenStorageUsageException($this->tag);
    }

    public function publicUrl(string $location): string
    {
        throw new ForbiddenStorageUsageException($this->tag);
    }

    public function temporaryUrl(string $location, DateTimeInterface $expiresAt): string
    {
        throw new ForbiddenStorageUsageException($this->tag);
    }

    public function checksum(string $location): string
    {
        throw new ForbiddenStorageUsageException($this->tag);
    }

    public function list(string $location = '', bool $deep = false): DirectoryListing
    {
        throw new ForbiddenStorageUsageException($this->tag);
    }
}
