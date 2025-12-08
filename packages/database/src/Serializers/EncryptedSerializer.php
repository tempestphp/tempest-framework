<?php

namespace Tempest\Database\Serializers;

use Tempest\Cryptography\Encryption\EncryptedData;
use Tempest\Cryptography\Encryption\Encrypter;
use Tempest\Cryptography\Encryption\Exceptions\EncryptionException;
use Tempest\Mapper\Serializer;
use Tempest\Support\Json\Exception\JsonException;

final readonly class EncryptedSerializer implements Serializer
{
    public function __construct(
        private Encrypter $encrypter,
    ) {}

    public static function for(): false
    {
        return false;
    }

    public function serialize(mixed $input): array|string
    {
        if (! is_string($input)) {
            return $input;
        }

        try {
            EncryptedData::unserialize($input);
        } catch (EncryptionException|JsonException) {
            return $this->encrypter->encrypt($input);
        }

        return $input;
    }
}
