<?php

namespace Tempest\Mail\Attachments;

use Closure;
use Tempest\Mail\Exceptions\FileAttachmentWasNotFound;
use Tempest\Support\Filesystem;
use Tempest\Support\Path;

/**
 * Represents an attachment that lives in the local filesystem.
 */
final class FileAttachment implements Attachment
{
    public Closure $resolve {
        get => fn () => Filesystem\read_file($this->path);
    }

    private function __construct(
        private readonly string $path,
        public readonly ?string $name,
        public readonly ?string $contentType,
    ) {}

    /**
     * Creates an attachment from the local filesystem.
     */
    public static function fromPath(string $path, ?string $name = null, ?string $contentType = null): self
    {
        $path = Path\normalize($path);

        if (! Filesystem\is_file($path)) {
            throw new FileAttachmentWasNotFound($path);
        }

        return new self(
            path: $path,
            name: $name ?? basename($path),
            contentType: $contentType ?? finfo_file(finfo_open(FILEINFO_MIME_TYPE), $path),
        );
    }
}
