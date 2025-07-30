<?php

namespace Tempest\Cryptography\Password;

interface PasswordHashingConfig
{
    public HashingAlgorithm $algorithm {
        get;
    }

    /**
     * Options for PHP's `password_hash` and `password_verify` functions.
     */
    public array $options {
        get;
    }
}
