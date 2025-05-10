<?php

namespace Tempest\Mail\Attachments;

use Closure;

/**
 * Represents an attachment that leaves in the local filesystem.
 */
final readonly class DataAttachment implements Attachment
{
    private function __construct(
        public Closure $resolve,
        public ?string $name,
        public ?string $contentType,
    ) {}

    /**
     * Creates an attachment from the given closure.
     */
    public static function fromClosure(Closure $closure, ?string $name = null, ?string $contentType = null): self
    {
        return new self($closure, $name, $contentType);
    }
}
