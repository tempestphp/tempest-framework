<?php

namespace Tempest\Cryptography\Tests;

use Tempest\Clock\Clock;
use Tempest\Clock\GenericClock;
use Tempest\Cryptography\Signing\GenericSigner;
use Tempest\Cryptography\Signing\SigningAlgorithm;
use Tempest\Cryptography\Signing\SigningConfig;
use Tempest\Cryptography\Timelock;

trait CreatesSigner
{
    private function createSigner(SigningConfig $config, ?Clock $clock = null): GenericSigner
    {
        return new GenericSigner(
            config: $config ?? new SigningConfig(
                algorithm: SigningAlgorithm::SHA256,
                key: 'my_secret_key',
                minimumExecutionDuration: false,
            ),
            timelock: new Timelock($clock ?? new GenericClock()),
        );
    }
}
