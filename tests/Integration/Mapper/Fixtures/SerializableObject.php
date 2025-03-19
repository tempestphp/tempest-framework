<?php

namespace Tests\Tempest\Integration\Mapper\Fixtures;

use Serializable;

final class SerializableObject implements Serializable
{
    public function serialize()
    {
        return 'a';
    }

    public function unserialize(string $data)
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