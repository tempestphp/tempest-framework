<?php

namespace Tempest\Cryptography\Signing;

use Tempest\Cryptography\Timelock;
use Tempest\DateTime\Duration;

final class GenericSigner implements Signer
{
    public SigningAlgorithm $algorithm {
        get => $this->config->algorithm;
    }

    private SigningKey $key {
        get => SigningKey::fromString($this->config->key);
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
            key: $this->key->value,
        ));
    }

    public function verify(string $data, Signature $signature): bool
    {
        return $this->timelock->invoke(
            callback: fn () => hash_equals(
                known_string: $this->sign($data)->value,
                user_string: $signature->value,
            ),
            duration: $this->config->minimumExecutionDuration ?: Duration::zero(),
        );
    }
}
