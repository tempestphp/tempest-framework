<?php

namespace Tempest\Cryptography\Signing;

use Stringable;
use Tempest\Cryptography\Signing\Exceptions\SigningKeyWasInvalid;

final readonly class SigningKey implements Stringable
{
    public function __construct(
        private(set) string $value,
    ) {
        if (trim($value) === '') {
            throw SigningKeyWasInvalid::becauseItIsMissing();
        }
    }

    /**
     * Creates a signing key from a string.
     */
    public static function fromString(string $key): self
    {
        return new self($key);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
