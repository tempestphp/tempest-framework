<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

use JsonSerializable;
use Tempest\Mapper\MapTo;

final readonly class ObjectWithMapToCollisionsJsonSerializable implements JsonSerializable
{
    public function __construct(
        #[MapTo('name')]
        public string $first_name,
        #[MapTo('full_name')]
        public string $name,
        public string $last_name,
    ) {}

    public function jsonSerialize(): mixed
    {
        return [
            'first_name' => $this->first_name,
            'name' => $this->name,
        ];
    }
}
