<?php

namespace Tempest\Mail;

use Closure;
use Tempest\Storage\Storage;
use UnitEnum;

use function Tempest\get;

/**
 * Represents an attachment that leaves in the {@see Tempest\Storage\Storage}.
 */
final class StorageAttachment implements Attachment
{
    public Closure $resolve {
        get => fn () => $this->storage->readStream($this->path);
    }

    private function __construct(
        private readonly string $path,
        public readonly ?string $name,
        public readonly ?string $contentType,
        private readonly Storage $storage,
    ) {}

    /**
     * Creates an attachment from the storage.
     */
    public static function fromPath(string $path, ?string $name = null, ?string $contentType = null, null|string|UnitEnum $tag = null): self
    {
        $storage = get(Storage::class, $tag);
        $name ??= basename($path);
        $contentType ??= $storage->mimeType($path);

        return new self(
            path: $path,
            name: $name,
            contentType: $contentType,
            storage: $storage,
        );
    }
}
