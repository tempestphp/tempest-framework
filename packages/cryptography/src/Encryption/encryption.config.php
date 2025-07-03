<?php

use Tempest\Cryptography\Encryption\EncryptionAlgorithm;
use Tempest\Cryptography\Encryption\EncryptionConfig;

return new EncryptionConfig(
    algorithm: EncryptionAlgorithm::AES_256_GCM,
    key: Tempest\env('SIGNING_KEY', default: ''),
);
