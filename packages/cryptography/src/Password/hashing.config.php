<?php

use Tempest\Cryptography\Password\ArgonConfig;
use Tempest\Cryptography\Password\BcryptConfig;
use Tempest\Cryptography\Password\HashingAlgorithm;

return in_array(HashingAlgorithm::ARGON2ID->value, password_algos(), true)
    ? new ArgonConfig()
    : new BcryptConfig();
