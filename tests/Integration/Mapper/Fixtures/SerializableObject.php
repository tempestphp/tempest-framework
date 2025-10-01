<?php

namespace Tests\Tempest\Integration\Mapper\Fixtures;

use Serializable;

final class SerializableObject implements Serializable
{
    /*
     * @mago-expect lint:return-type
     * @phpstan-ignore return.unusedType
     */
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

    public function __unserialize(array $_data): void
    {
        // nothing
    }
}
