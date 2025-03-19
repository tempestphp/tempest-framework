<?php

namespace Tests\Tempest\Integration\Mapper\Fixtures;

use Serializable;

final class SerializableObject implements Serializable
{
    /* @phpstan-ignore-next-line */
    public function serialize()
    {
        return 'a';
    }

    public function unserialize(string $data): void
    {
        // nothing
    }

    public function __serialize(): array
    {
        return ['a'];
    }

    public function __unserialize(array $data): void
    {
        // nothing
    }
}