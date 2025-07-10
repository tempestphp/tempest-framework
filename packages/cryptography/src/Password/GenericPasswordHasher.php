<?php

namespace Tempest\Cryptography\Password;

use Tempest\Cryptography\Password\Exceptions\HashingFailed;
use Tempest\Cryptography\Password\HashingAlgorithm;

final class GenericPasswordHasher implements PasswordHasher
{
    public HashingAlgorithm $algorithm {
        get => $this->config->algorithm;
    }

    public function __construct(
        private readonly PasswordHashingConfig $config,
    ) {}

    public function hash(#[\SensitiveParameter] string $password): string
    {
        $hash = password_hash($password, $this->algorithm->value, $this->config->options);

        if ($hash === false) {
            throw HashingFailed::forUnknownReason();
        }

        if (mb_strlen($password) === 0) {
            throw HashingFailed::forEmptyPassword();
        }

        return $hash;
    }

    public function verify(#[\SensitiveParameter] string $password, #[\SensitiveParameter] string $hash): bool
    {
        if (mb_strlen($hash) === 0) {
            return false;
        }

        return password_verify($password, $hash);
    }

    public function needsRehash(#[\SensitiveParameter] string $hash): bool
    {
        return password_needs_rehash($hash, $this->algorithm->value, $this->config->options);
    }

    public function analyze(#[\SensitiveParameter] string $hash): Hash
    {
        $info = password_get_info($hash);
        $algorithm = HashingAlgorithm::from($info['algo']);

        return new Hash(
            hash: $hash,
            algorithm: $algorithm,
            config: match ($algorithm) {
                HashingAlgorithm::BCRYPT => new BcryptConfig(
                    cost: $info['options']['cost'] ?? PASSWORD_BCRYPT_DEFAULT_COST,
                ),
                HashingAlgorithm::ARGON2ID => new ArgonConfig(
                    memoryCost: $info['options']['memory_cost'] ?? PASSWORD_ARGON2_DEFAULT_MEMORY_COST,
                    timeCost: $info['options']['time_cost'] ?? PASSWORD_ARGON2_DEFAULT_TIME_COST,
                    threads: $info['options']['threads'] ?? PASSWORD_ARGON2_DEFAULT_THREADS,
                ),
            },
        );
    }
}
