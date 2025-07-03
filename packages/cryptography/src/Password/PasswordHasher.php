<?php

namespace Tempest\Cryptography\Password;

use Tempest\Cryptography\Password\HashingAlgorithm;

interface PasswordHasher
{
    public HashingAlgorithm $algorithm {
        get;
    }

    /**
     * Hashes the specified password.
     */
    public function hash(#[\SensitiveParameter] string $password): string;

    /**
     * Checks if the given pain-text password matches the given hash.
     */
    public function verify(#[\SensitiveParameter] string $password, string $hash): bool;

    /**
     * Checks if the given hash needs to be rehashed, which happens
     * when the initial hash was created with different algorithm options.
     */
    public function needsRehash(#[\SensitiveParameter] string $hash): bool;

    /**
     * Returns informations about the given hash, such as the algorithm used and its options.
     */
    public function analyze(#[\SensitiveParameter] string $hash): Hash;
}
