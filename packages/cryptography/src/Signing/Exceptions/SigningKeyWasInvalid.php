<?php

namespace Tempest\Cryptography\Signing\Exceptions;

use Exception;

final class SigningKeyWasInvalid extends Exception implements SigningException
{
    public static function becauseItIsMissing(): self
    {
        return new self('The signing key is missing or empty. Ensure you have a `SIGNING_KEY` environment variable.');
    }
}
