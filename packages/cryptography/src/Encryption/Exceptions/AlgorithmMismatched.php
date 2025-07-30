<?php

namespace Tempest\Cryptography\Encryption\Exceptions;

use Exception;

final class AlgorithmMismatched extends Exception implements EncryptionException
{
    public static function betweenKeyAndData(): self
    {
        return new self('The encryption algorithm used for the key does not match the algorithm used for the data. Ensure that both are using the same algorithm.');
    }
}
