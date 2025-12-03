<?php

namespace Tempest\Cryptography\Password;

use Error;
use Tempest\Cryptography\Password\Exceptions\HashingFailed;
use ValueError;

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
        if ($password === '') {
            throw HashingFailed::forEmptyPassword();
        }

        try {
            $hash = password_hash($password, $this->algorithm->value, $this->config->options);
        } catch (ValueError) {
            throw HashingFailed::forInvalidHashingAlgorithm();
        } catch (Error) {
            throw HashingFailed::forUnknownReason();
        }

        return $hash;
    }

    public function verify(#[\SensitiveParameter] string $password, #[\SensitiveParameter] string $hash): bool
    {
        if ($password === '' || $hash === '') {
            return false;
        }

        return password_verify($password, $hash);
    }

    public function needsRehash(#[\SensitiveParameter] string $hash): bool
    {
        if ($hash === '') {
            return false;
        }

        return password_needs_rehash($hash, $this->algorithm->value, $this->config->options);
    }

    public function analyze(#[\SensitiveParameter] string $hash): ?Hash
    {
        if ($hash === '') {
            return null;
        }

        $info = password_get_info($hash);

        if ($info['algo'] === null) {
            return null;
        }

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
