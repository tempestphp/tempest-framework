<?php

declare(strict_types=1);

namespace Tempest\Http;

use BackedEnum;

final class Header
{
    public function __construct(
        public string $name,
        /** @var array<array-key,mixed> $values */
        public array $values = [],
    ) {}

    public function add(mixed $value): void
    {
        if ($value instanceof BackedEnum) {
            $value = $value->value;
        }

        $this->values[] = $value;
    }

    public function first(): mixed
    {
        return array_first($this->values);
    }
}
