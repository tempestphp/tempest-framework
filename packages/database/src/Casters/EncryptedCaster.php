<?php

declare(strict_types=1);

namespace Tempest\Database\Casters;

use Tempest\Cryptography\Encryption\Encrypter;
use Tempest\Mapper\Caster;

final readonly class EncryptedCaster implements Caster
{
    public function __construct(
        private Encrypter $encrypter,
    ) {}

    public function cast(mixed $input): ?string
    {
        if ($input === null) {
            return null;
        }

        return $this->encrypter->decrypt($input);
    }
}
