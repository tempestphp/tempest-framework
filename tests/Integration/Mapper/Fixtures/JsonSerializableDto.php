<?php

namespace Tests\Tempest\Integration\Mapper\Fixtures;

use JsonSerializable;
use Tempest\Mapper\SerializeAs;

#[SerializeAs(self::class)]
final class JsonSerializableDto implements JsonSerializable
{
    public function jsonSerialize(): array
    {
        return ['serialized' => 'data'];
    }
}
