<?php

namespace Tempest\Database\Serializers;

use Tempest\Cryptography\Password\PasswordHasher;
use Tempest\Mapper\Exceptions\ValueCouldNotBeSerialized;
use Tempest\Mapper\Serializer;

final readonly class HashedSerializer implements Serializer
{
    public function __construct(
        private PasswordHasher $passwordHasher,
    ) {}

    public function serialize(mixed $input): string
    {
        if (! is_string($input)) {
            throw new ValueCouldNotBeSerialized('string');
        }

        if (! $this->passwordHasher->analyze($input)) {
            return $this->passwordHasher->hash($input);
        }

        return $input;
    }
}
