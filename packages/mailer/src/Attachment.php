<?php

namespace Tempest\Mail;

use Closure;
use Tempest\Mail\Exceptions\FileAttachmentWasNotFound;
use Tempest\Storage\Storage;
use Tempest\Support\Filesystem;
use Tempest\Support\Path;

final readonly class Attachment
{
    /**
     * Creates an attachment that can be emailed.
     *
     * @param Closure $resolve Resolves the attachment to raw data.
     * @param null|string $name File name of the attachment.
     * @param null|string $contentType Content type of the attachment.
     */
    public function __construct(
        public \Closure $resolve,
        public ?string $name = null,
        public ?string $contentType = null,
    ) {}

    /**
     * Creates an attachment from the given closure.
     */
    public static function fromClosure(callable $callable, ?string $name = null, ?string $contentType = null): self
    {
        return new self(
            resolve: Closure::fromCallable($callable),
            name: $name,
            contentType: $contentType,
        );
    }

    /**
     * Creates an attachment from the storage.
     */
    public static function fromStorage(Storage $storage, string $path, ?string $name = null, ?string $contentType = null): self
    {
        if (! $storage->fileOrDirectoryExists($path)) {
            throw FileAttachmentWasNotFound::forStorageFile($path);
        }

        $path = Path\normalize($path);

        return new self(
            resolve: fn () => $storage->readStream($path),
            name: $name ?? basename($path),
            contentType: $contentType ?? $storage->mimeType($path),
        );
    }

    /**
     * Creates an attachment from the local filesystem.
     */
    public static function fromFilesystem(string $path, ?string $name = null, ?string $contentType = null): self
    {
        $path = Path\normalize($path);

        if (! Filesystem\is_file($path)) {
            throw new FileAttachmentWasNotFound($path);
        }

        return new self(
            resolve: fn () => Filesystem\read_file($path),
            name: $name ?? basename($path),
            contentType: $contentType ?? finfo_file(finfo_open(FILEINFO_MIME_TYPE), $path),
        );
    }
}
