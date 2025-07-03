<?php

namespace Tempest\Cryptography\Encryption;

use Stringable;
use Tempest\Cryptography\Encryption\Exceptions\EncryptionKeyWasInvalid;

final readonly class EncryptionKey implements Stringable
{
    public function __construct(
        private(set) string $value,
        private(set) EncryptionAlgorithm $algorithm,
    ) {
        if (trim($value) === '') {
            throw EncryptionKeyWasInvalid::becauseItIsMissing($algorithm);
        }

        if (strlen($value) !== $algorithm->getKeyLength()) {
            throw EncryptionKeyWasInvalid::becauseLengthMismatched($algorithm);
        }
    }

    /**
     * Generates a new cryptographically secure key using the specified algorithm.
     */
    public static function generate(EncryptionAlgorithm $algorithm): self
    {
        return new self(random_bytes($algorithm->getKeyLength()), $algorithm);
    }

    /**
     * Creates an encryption key from a string.
     */
    public static function fromString(string $key, EncryptionAlgorithm $algorithm): self
    {
        return new self($key, $algorithm);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
