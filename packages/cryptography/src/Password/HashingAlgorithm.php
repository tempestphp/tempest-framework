<?php

namespace Tempest\Cryptography\Password;

enum HashingAlgorithm: string
{
    // The values are the literal strings behind PASSWORD_ARGON2ID and PASSWORD_BCRYPT.
    // PASSWORD_ARGON2ID is only defined on PHP builds compiled with Argon2, so using the
    // constant here would make the whole enum unloadable on builds without it. The literals
    // match what password_hash() expects and what password_get_info() reports.

    /**
     * @see https://en.wikipedia.org/wiki/Argon2
     */
    case ARGON2ID = 'argon2id';

    /**
     * @see https://en.wikipedia.org/wiki/bcrypt
     */
    case BCRYPT = '2y';
}
