<?php

namespace Tempest\Mail\Testing;

use PHPUnit\Framework\Assert;
use Symfony\Component\Mime\Part\DataPart;

final class AttachmentTester
{
    /**
     * Headers associated with this attachment.
     */
    public array $headers {
        get => $this->original->getHeaders()->toArray();
    }

    /**
     * Body of this attachment.
     */
    public string $body {
        get => $this->original->bodyToString();
    }

    /**
     * Name of this attachment.
     */
    public string $name {
        get => $this->original->getFilename();
    }

    /**
     * Type of this attachment.
     */
    public string $mediaType {
        get => $this->original->getMediaType();
    }

    public function __construct(
        private DataPart $original,
    ) {}

    /**
     * Asserts that the content of the attachment matches
     */
    public function assertContent(string $expected): self
    {
        Assert::assertSame(
            expected: $expected,
            actual: $this->body,
            message: "Failed asserting that attachment content is `{$expected}`. Actual content is `{$this->body}`",
        );

        return $this;
    }

    /**
     * Asserts that the attachment has the given name.
     */
    public function assertNamed(string $name): self
    {
        Assert::assertSame(
            expected: $name,
            actual: $this->name,
            message: "Failed asserting that attachment name is `{$name}`. Actual name is `{$this->name}`",
        );

        return $this;
    }

    /**
     * Asserts that the attachment does not have the given name.
     */
    public function assertNotNamed(string $name): void
    {
        Assert::assertNotSame(
            expected: $name,
            actual: $this->name,
            message: "Failed asserting that attachment name is not `{$name}`.",
        );
    }

    /**
     * Asserts that the attachment has the given type.
     */
    public function assertType(string $type): self
    {
        Assert::assertSame(
            expected: $type,
            actual: $this->mediaType,
            message: "Failed asserting that attachment type is `{$type}`. Actual type is `{$this->mediaType}`",
        );

        return $this;
    }

    /**
     * Asserts that the attachment does not have the given type.
     */
    public function assertNotType(string $type): self
    {
        Assert::assertNotSame(
            expected: $type,
            actual: $this->mediaType,
            message: "Failed asserting that attachment type is not `{$type}`.",
        );

        return $this;
    }
}
