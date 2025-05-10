<?php

namespace Tempest\Mail\Attachments;

use Closure;

/**
 * Represents an attachment that leaves in the local filesystem.
 */
final class DataAttachment implements Attachment
{
    private function __construct(
        public readonly Closure $resolve,
        public readonly ?string $name,
        public readonly ?string $contentType,
    ) {}

    /**
     * Creates an attachment from the given closure.
     */
    public static function fromClosure(Closure $closure, ?string $name = null, ?string $contentType = null): self
    {
        return new self($closure, $name, $contentType);
    }
}
