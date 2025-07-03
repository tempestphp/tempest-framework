<?php

namespace Tempest\Cryptography\Signing;

final class SigningConfig
{
    /**
     * @param SigningAlgorithm $algorithm The algorithm used for signing and verifying signatures.
     * @param non-empty-string $key The key used for signing and verifying signatures.
     */
    public function __construct(
        public SigningAlgorithm $algorithm,
        #[\SensitiveParameter]
        public string $key,
    ) {}
}
