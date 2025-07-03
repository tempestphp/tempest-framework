<?php

namespace Tempest\Cryptography\Signing;

use Tempest\Cryptography\Signing\Exceptions\SigningKeyWasMissing;

final class GenericSigner implements Signer
{
    public SigningAlgorithm $algorithm {
        get => $this->config->algorithm;
    }

    private string $key {
        get {
            if (trim($this->config->key) === '') {
                throw new SigningKeyWasMissing();
            }

            return $this->config->key;
        }
    }

    public function __construct(
        private readonly SigningConfig $config,
    ) {}

    public function sign(string $data): Signature
    {
        return new Signature(hash_hmac(
            algo: $this->algorithm->value,
            data: $data,
            key: $this->key,
        ));
    }

    public function verify(string $data, Signature $signature): bool
    {
        return hash_equals(
            known_string: $this->sign($data)->signature,
            user_string: $signature->signature,
        );
    }
}
