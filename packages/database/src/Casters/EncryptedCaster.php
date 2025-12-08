<?php

declare(strict_types=1);

namespace Tempest\Database\Casters;

use Tempest\Cryptography\Encryption\Encrypter;
use Tempest\Cryptography\Encryption\Exceptions\EncryptionException;
use Tempest\Mapper\Caster;
use Tempest\Support\Json\Exception\JsonException;

final readonly class EncryptedCaster implements Caster
{
    public function __construct(
        private Encrypter $encrypter,
    ) {}

    public static function for(): false
    {
        return false;
    }

    public function cast(mixed $input): ?string
    {
        if ($input === null) {
            return null;
        }

        try {
            return $this->encrypter->decrypt($input);
        } catch (EncryptionException|JsonException) {
            return $input;
        }
    }
}
