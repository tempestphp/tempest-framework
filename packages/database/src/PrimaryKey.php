<?php

declare(strict_types=1);

namespace Tempest\Database;

use Stringable;

final readonly class PrimaryKey implements Stringable
{
    public string|int $value;

    public static function tryFrom(string|int|self|null $value): ?self
    {
        if ($value === null) {
            return null;
        }

        return new self($value);
    }

    public function __construct(string|int|self $value)
    {
        $value = $value instanceof self
            ? $value->value
            : $value;

        $this->value = is_numeric($value)
            ? (int) $value
            : $value;
    }

    public function __toString(): string
    {
        return "{$this->value}";
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
