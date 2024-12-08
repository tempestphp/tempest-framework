<?php

declare(strict_types=1);

namespace Tempest\Console\Components;

/**
 * @template T
 */
final readonly class Option
{
    public function __construct(
        public int|string $key,
        /** @var T $value */
        public mixed $value,
    ) {
    }

    public function equals(Option $other): bool
    {
        return $other->key === $this->key && $other->value === $this->value;
    }
}
