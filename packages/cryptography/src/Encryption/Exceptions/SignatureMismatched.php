<?php

namespace Tempest\Cryptography\Encryption\Exceptions;

use Exception;

final class SignatureMismatched extends Exception implements EncryptionException
{
    public static function raise(): self
    {
        return new self('The signature does not match the data. This could indicate that the data has been tampered with or that the wrong key was used for verification.');
    }
}
