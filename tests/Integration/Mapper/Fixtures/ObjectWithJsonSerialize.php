<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

use JsonSerializable;
use Tempest\Mapper\Strict;

#[Strict]
final class ObjectWithJsonSerialize implements JsonSerializable
{
    public function __construct(
        public string $a,
        public string $b,
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'c' => $this->a,
            'd' => $this->b,
        ];
    }
}
