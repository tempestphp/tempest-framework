<?php

namespace Tempest\Cryptography\Encryption\Exceptions;

use Exception;

final class EncryptedDataWasInvalid extends Exception implements EncryptionException
{
    public static function dueToInvalidFormat(): self
    {
        return new self('The encrypted data is not in the expected format.');
    }
}
