<?php

namespace Tempest\Cryptography\Signing\Exceptions;

use Exception;

final class SigningKeyWasMissing extends Exception implements SigningException
{
    public function __construct()
    {
        parent::__construct('Signing key is not configured. Ensure you have a `SIGNING_KEY` environment variable.');
    }
}
