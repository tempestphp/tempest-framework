<?php

namespace Tempest\Cryptography\Encryption\Exceptions;

use Exception;
use Tempest\Cryptography\Encryption\EncryptionAlgorithm;

final class EncryptionKeyWasInvalid extends Exception implements EncryptionException
{
    public function __construct(
        string $message,
        public readonly EncryptionAlgorithm $algorithm,
    ) {
        parent::__construct($message);
    }

    public static function becauseItIsMissing(EncryptionAlgorithm $algorithm): self
    {
        return new self('The encryption key is missing or empty. Ensure you have a `SIGNING_KEY` environment variable.', $algorithm);
    }

    public static function becauseLengthMismatched(EncryptionAlgorithm $algorithm): self
    {
        return new self(
            "The encryption key length does not match the expected length ({$algorithm->getKeyLength()}).",
            $algorithm,
        );
    }
}
