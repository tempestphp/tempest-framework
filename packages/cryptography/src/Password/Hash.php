<?php

namespace Tempest\Cryptography\Password;

use SensitiveParameter;

final readonly class Hash
{
    public function __construct(
        #[SensitiveParameter]
        public string $hash,
        public HashingAlgorithm $algorithm,
        public PasswordHashingConfig $config,
    ) {}
}
