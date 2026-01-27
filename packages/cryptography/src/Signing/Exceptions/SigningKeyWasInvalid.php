<?php

namespace Tempest\Cryptography\Signing\Exceptions;

use Exception;

final class SigningKeyWasInvalid extends Exception implements SigningException
{
    public static function becauseItIsMissing(): self
    {
        return new self('The signing key is missing or empty. Generate a valid `SIGNING_KEY` with `php tempest key:generate`.');
    }
}
