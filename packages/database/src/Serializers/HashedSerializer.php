<?php

namespace Tempest\Database\Serializers;

use Tempest\Cryptography\Password\PasswordHasher;
use Tempest\Mapper\Serializer;

final readonly class HashedSerializer implements Serializer
{
    public function __construct(
        private PasswordHasher $passwordHasher,
    ) {}

    public static function for(): false
    {
        return false;
    }

    public function serialize(mixed $input): string
    {
        if (! is_string($input)) {
            return $input;
        }

        if (! $this->passwordHasher->analyze($input)) {
            return $this->passwordHasher->hash($input);
        }

        return $input;
    }
}
