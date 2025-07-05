<?php

namespace Tempest\Cryptography\Signing;

interface Signer
{
    public SigningAlgorithm $algorithm {
        get;
    }

    /**
     * Signs the given data.
     */
    public function sign(string $data): Signature;

    /**
     * Verifies the integrity and provenance of the given data thanks to the given user-provided signature.
     */
    public function verify(string $data, Signature $signature): bool;
}
