<?php

use Tempest\Cryptography\Signing\SigningAlgorithm;
use Tempest\Cryptography\Signing\SigningConfig;

return new SigningConfig(
    algorithm: SigningAlgorithm::SHA256,
    key: Tempest\env('SIGNING_KEY', default: ''),
);
