<?php

namespace Tempest\Cryptography\Signing;

use Tempest\Cryptography\Signing\Exceptions\SigningKeyWasMissing;
use Tempest\Cryptography\Timelock;
use Tempest\DateTime\Duration;

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
        private readonly Timelock $timelock,
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
        return $this->timelock->invoke(
            callback: fn () => hash_equals(
                known_string: $this->sign($data)->signature,
                user_string: $signature->signature,
            ),
            duration: $this->config->minimumExecutionDuration ?: Duration::zero(),
        );
    }
}
