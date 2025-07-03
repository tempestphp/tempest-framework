<?php

namespace Tempest\Cryptography\Signing;

enum SigningAlgorithm: string
{
    case SHA256 = 'sha256';
    case SHA512 = 'sha512';
    case SHA3_256 = 'sha3-256';
    case SHA3_512 = 'sha3-512';
}
