<?php

namespace Tempest\Cryptography\Password;

use const PASSWORD_ARGON2ID;
use const PASSWORD_BCRYPT;

enum HashingAlgorithm: string
{
    /**
     * @see https://en.wikipedia.org/wiki/Argon2
     */
    case ARGON2ID = PASSWORD_ARGON2ID;

    /**
     * @see https://en.wikipedia.org/wiki/bcrypt
     */
    case BCRYPT = PASSWORD_BCRYPT;
}
