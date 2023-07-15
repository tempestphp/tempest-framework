<?php

declare(strict_types=1);

namespace Tempest\Database;

use Tempest\ORM\Attributes\CastWith;
use Tempest\ORM\Casters\IdCaster;

#[CastWith(IdCaster::class)]
final readonly class Id
{
    public function __construct(
        public string|int $id,
    ) {
    }

    public function __toString(): string
    {
        return "{$this->id}";
    }
}
