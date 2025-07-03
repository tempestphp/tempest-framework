<?php

namespace Tempest\Cryptography\Password;

use Tempest\Cryptography\Password\HashingAlgorithm;

final class BcryptConfig implements PasswordHashingConfig
{
    public HashingAlgorithm $algorithm = HashingAlgorithm::BCRYPT;

    public array $options {
        get => [
            'cost' => $this->cost,
        ];
    }

    /**
     * @param int $cost Number of iterations bcrypt will perform. Increasing this increases the computation time but makes brute-force attacks slower.
     */
    public function __construct(
        public int $cost = PASSWORD_BCRYPT_DEFAULT_COST,
    ) {}
}
