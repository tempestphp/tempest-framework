<?php

namespace Tempest\Cryptography\Signing;

use Tempest\DateTime\Duration;

final class SigningConfig
{
    /**
     * @param SigningAlgorithm $algorithm The algorithm used for signing and verifying signatures.
     * @param non-empty-string $key The key used for signing and verifying signatures.
     * @param Duration|false $minimumExecutionDuration The minimum execution duration for signing operations, to prevent timing attacks. Set `false` to disable timing attack protection.
     */
    public function __construct(
        public SigningAlgorithm $algorithm,
        #[\SensitiveParameter]
        public string $key,
        public false|Duration $minimumExecutionDuration,
    ) {}
}
