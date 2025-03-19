<?php

namespace Tests\Tempest\Integration\Mapper\Fixtures;

use JsonSerializable;

final class JsonSerializableObject implements JsonSerializable
{
    public function jsonSerialize(): array
    {
        return ['a'];
    }
}