<?php

namespace Tempest\Storage;

use DateTimeInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\ReadOnly\ReadOnlyFilesystemAdapter;
use League\Flysystem\UrlGeneration\TemporaryUrlGenerator;
use Tempest\Storage\Config\StorageConfig;

final class GenericStorage implements Storage
{
    public function __construct(
        private StorageConfig $storageConfig,
        private ?Filesystem $filesystem = null,
        ?TemporaryUrlGenerator $temporaryUrlGenerator = null,
    ) {
        $this->filesystem ??= new Filesystem(
            adapter: $this->createAdapter(),
            temporaryUrlGenerator: $temporaryUrlGenerator,
        );
    }

    public function write(string $location, string $contents): static
    {
        $this->filesystem->write($location, $contents);

        return $this;
    }

    public function writeStream(string $location, mixed $contents): static
    {
        $this->filesystem->writeStream($location, $contents);

        return $this;
    }

    public function read(string $location): string
    {
        return $this->filesystem->read($location);
    }

    public function readStream(string $location): mixed
    {
        return $this->filesystem->readStream($location);
    }

    public function fileExists(string $location): bool
    {
        return $this->filesystem->fileExists($location);
    }

    public function directoryExists(string $location): bool
    {
        return $this->filesystem->directoryExists($location);
    }

    public function fileOrDirectoryExists(string $location): bool
    {
        return $this->filesystem->has($location);
    }

    public function delete(string $location): static
    {
        $this->filesystem->delete($location);

        return $this;
    }

    public function deleteDirectory(?string $location = ''): static
    {
        $this->filesystem->deleteDirectory($location);

        return $this;
    }

    public function createDirectory(?string $location = ''): static
    {
        $this->filesystem->createDirectory($location);

        return $this;
    }

    public function cleanDirectory(?string $location = ''): static
    {
        $this->filesystem->deleteDirectory($location);
        $this->filesystem->createDirectory($location);

        return $this;
    }

    public function move(string $source, string $destination): static
    {
        $this->filesystem->move($source, $destination);

        return $this;
    }

    public function copy(string $source, string $destination): static
    {
        $this->filesystem->copy($source, $destination);

        return $this;
    }

    public function fileSize(string $location): int
    {
        return $this->filesystem->fileSize($location);
    }

    public function lastModified(string $location): int
    {
        return $this->filesystem->lastModified($location);
    }

    public function mimeType(string $location): string
    {
        return $this->filesystem->mimeType($location);
    }

    public function setVisibility(string $location, string $visibility): static
    {
        $this->filesystem->setVisibility($location, $visibility);

        return $this;
    }

    public function visibility(string $location): string
    {
        return $this->filesystem->visibility($location);
    }

    public function publicUrl(string $location): string
    {
        return $this->filesystem->publicUrl($location);
    }

    public function temporaryUrl(string $location, DateTimeInterface $expiresAt): string
    {
        return $this->filesystem->temporaryUrl($location, $expiresAt);
    }

    public function checksum(string $location): string
    {
        return $this->filesystem->checksum($location);
    }

    public function list(string $location = '', bool $deep = false): DirectoryListing
    {
        return new DirectoryListing($this->filesystem->listContents($location, $deep));
    }

    private function createAdapter(): FilesystemAdapter
    {
        $this->assertAdapterInstalled($this->storageConfig->adapter);

        $adapter = $this->storageConfig->createAdapter();

        if (! $this->storageConfig->readonly) {
            return $adapter;
        }

        $this->assertAdapterInstalled(ReadOnlyFilesystemAdapter::class);

        return new ReadOnlyFilesystemAdapter($adapter);
    }

    private function assertAdapterInstalled(string $adapter): void
    {
        if (! class_exists($adapter)) {
            throw new MissingAdapterException($adapter);
        }
    }
}
