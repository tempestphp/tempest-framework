<?php

declare(strict_types=1);

namespace Tempest\Database;

use Stringable;
use Tempest\Database\Casters\IdCaster;
use Tempest\Mapper\CastWith;

#[CastWith(IdCaster::class)]
final readonly class Id implements Stringable
{
    public string|int $id;

    public static function tryFrom(string|int|self|null $id): ?self
    {
        if ($id === null) {
            return null;
        }

        return new self($id);
    }

    public function __construct(string|int|self $id)
    {
        $id = ($id instanceof self) ? $id->id : $id;

        $this->id = is_numeric($id) ? ((int) $id) : $id;
    }

    public function __toString(): string
    {
        return "{$this->id}";
    }

    public function equals(self $other): bool
    {
        return $this->id === $other->id;
    }
}
