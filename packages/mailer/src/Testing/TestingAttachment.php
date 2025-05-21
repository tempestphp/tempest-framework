<?php

namespace Tempest\Mail\Testing;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\ExpectationFailedException;
use Symfony\Component\Mime\Part\DataPart;

final class TestingAttachment
{
    /**
     * Headers associated to this attachment.
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
    public string $type {
        get => $this->original->getMediaType();
    }

    public function __construct(
        private DataPart $original,
    ) {}

    /**
     * Asserts that the attachment has the given name.
     */
    public function assertNamed(string $name): void
    {
        Assert::assertSame(
            expected: $name,
            actual: $this->name,
            message: "Failed asserting that attachment name is `{$name}`. Actual name is `{$this->name}`",
        );
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
    public function assertType(string $type): void
    {
        Assert::assertSame(
            expected: $type,
            actual: $this->type,
            message: "Failed asserting that attachment type is `{$type}`. Actual type is `{$this->type}`",
        );
    }

    /**
     * Asserts that the attachment does not have the given type.
     */
    public function assertNotType(string $type): void
    {
        Assert::assertNotSame(
            expected: $type,
            actual: $this->type,
            message: "Failed asserting that attachment type is not `{$type}`.",
        );
    }
}
