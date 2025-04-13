<?php

namespace Tempest\Storage\Testing;

use DateTimeInterface;
use League\Flysystem\UrlGeneration\PublicUrlGenerator;
use League\Flysystem\UrlGeneration\TemporaryUrlGenerator;
use Tempest\Storage\Config\LocalStorageConfig;
use Tempest\Storage\DirectoryListing;
use Tempest\Storage\GenericStorage;
use Tempest\Storage\Storage;

use function Tempest\internal_storage_path;

final class TestingStorage implements Storage
{
    private Storage $storage;

    private ?TemporaryUrlGenerator $temporaryUrlGenerator = null;

    private ?PublicUrlGenerator $publicUrlGenerator = null;

    public function __construct(
        private readonly ?string $path = null,
        ?TemporaryUrlGenerator $temporaryUrlGenerator = null,
        ?PublicUrlGenerator $publicUrlGenerator = null,
    ) {
        $this->storage = $this->createStorage($path, $temporaryUrlGenerator, $publicUrlGenerator);
    }

    public function setTemporaryUrlGenerator(TemporaryUrlGenerator $generator): static
    {
        $this->temporaryUrlGenerator = $generator;
        $this->storage = $this->createStorage($this->path, temporaryUrlGenerator: $generator);

        return $this;
    }

    public function setPublicUrlGenerator(PublicUrlGenerator $generator): static
    {
        $this->publicUrlGenerator = $generator;
        $this->storage = $this->createStorage($this->path, publicUrlGenerator: $generator);

        return $this;
    }

    public function write(string $location, string $contents): static
    {
        $this->storage->write($location, $contents);

        return $this;
    }

    public function writeStream(string $location, mixed $contents): static
    {
        $this->storage->writeStream($location, $contents);

        return $this;
    }

    public function read(string $location): string
    {
        return $this->storage->read($location);
    }

    public function readStream(string $location): mixed
    {
        return $this->storage->readStream($location);
    }

    public function fileExists(string $location): bool
    {
        return $this->storage->fileExists($location);
    }

    public function directoryExists(string $location): bool
    {
        return $this->storage->directoryExists($location);
    }

    public function fileOrDirectoryExists(string $location): bool
    {
        return $this->storage->fileOrDirectoryExists($location);
    }

    public function delete(string $location): static
    {
        $this->storage->delete($location);

        return $this;
    }

    public function deleteDirectory(?string $location = ''): static
    {
        $this->storage->deleteDirectory($location);

        return $this;
    }

    public function createDirectory(?string $location = ''): static
    {
        $this->storage->createDirectory($location);

        return $this;
    }

    public function cleanDirectory(?string $location = ''): static
    {
        $this->storage->cleanDirectory($location);

        return $this;
    }

    public function move(string $source, string $destination): static
    {
        $this->storage->move($source, $destination);

        return $this;
    }

    public function copy(string $source, string $destination): static
    {
        $this->storage->copy($source, $destination);

        return $this;
    }

    public function fileSize(string $location): int
    {
        return $this->storage->fileSize($location);
    }

    public function lastModified(string $location): int
    {
        return $this->storage->lastModified($location);
    }

    public function mimeType(string $location): string
    {
        return $this->storage->mimeType($location);
    }

    public function setVisibility(string $location, string $visibility): static
    {
        $this->storage->setVisibility($location, $visibility);

        return $this;
    }

    public function visibility(string $location): string
    {
        return $this->storage->visibility($location);
    }

    public function publicUrl(string $location): string
    {
        return $this->storage->publicUrl($location);
    }

    public function temporaryUrl(string $location, DateTimeInterface $expiresAt): string
    {
        return $this->storage->temporaryUrl($location, $expiresAt);
    }

    public function checksum(string $location): string
    {
        return $this->storage->checksum($location);
    }

    public function list(string $location = '', bool $deep = false): DirectoryListing
    {
        return $this->storage->list($location, $deep);
    }

    private function createStorage(?string $path = null, ?TemporaryUrlGenerator $temporaryUrlGenerator = null, ?PublicUrlGenerator $publicUrlGenerator = null): Storage
    {
        return new GenericStorage(
            storageConfig: new LocalStorageConfig(
                path: internal_storage_path('tests/storage/' . ($path ?? 'storage')),
                readonly: false,
            ),
            temporaryUrlGenerator: $temporaryUrlGenerator ?? $this->temporaryUrlGenerator,
            publicUrlGenerator: $publicUrlGenerator ?? $this->publicUrlGenerator,
        );
    }
}
