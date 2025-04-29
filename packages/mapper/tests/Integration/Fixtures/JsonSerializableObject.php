<?php

namespace Tempest\Mapper\Tests\Integration\Fixtures;

use JsonSerializable;

final class JsonSerializableObject implements JsonSerializable
{
    public function jsonSerialize(): array
    {
        return ['a'];
    }
}
