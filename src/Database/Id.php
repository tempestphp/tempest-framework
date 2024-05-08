<?php

declare(strict_types=1);

namespace Tempest\Database;

use Tempest\Mapper\CastWith;
use Tempest\ORM\Casters\IdCaster;

#[CastWith(IdCaster::class)]
final readonly class Id
{
    public string|int $id;

    public function __construct(string|int|self $id)
    {
        $this->id = $id instanceof self ? $id->id : $id;
    }

    public function __toString(): string
    {
        return "{$this->id}";
    }
}
