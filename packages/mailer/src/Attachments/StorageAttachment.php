<?php

namespace Tempest\Mail\Attachments;

use Closure;
use Tempest\Container\GenericContainer;
use Tempest\Storage\Storage;
use Tempest\Support\Path;
use UnitEnum;

/**
 * Represents an attachment that lives in the {@see Tempest\Storage\Storage}.
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
        if (! ($storage = self::resolveStorage($tag))) {
            throw new \RuntimeException('No storage found.');
        }

        $path = Path\normalize($path);

        return new self(
            path: $path,
            name: $name ?? basename($path),
            contentType: $contentType ?? $storage->mimeType($path),
            storage: $storage,
        );
    }

    private static function resolveStorage(null|string|UnitEnum $tag = null): ?Storage
    {
        if (! class_exists(GenericContainer::class)) {
            return null;
        }

        if (is_null(GenericContainer::instance())) {
            return null;
        }

        if (! GenericContainer::instance()->has(Storage::class, $tag)) {
            return null;
        }

        return GenericContainer::instance()->get(Storage::class, $tag);
    }
}
