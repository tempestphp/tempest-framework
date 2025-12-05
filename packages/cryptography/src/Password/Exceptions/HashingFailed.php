<?php

namespace Tempest\Cryptography\Password\Exceptions;

use Exception;

final class HashingFailed extends Exception implements PasswordHashingException
{
    public static function forUnknownReason(): self
    {
        return new self('Hashing resulted in an error.');
    }

    public static function forEmptyPassword(): self
    {
        return new self('Could not hash an empty password.');
    }

    public static function forInvalidHashingAlgorithm(): self
    {
        return new self('The specified hashing algorithm is not supported.');
    }
}
