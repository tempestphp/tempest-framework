<?php

declare(strict_types=1);

namespace Tempest\Console\Components;

use ReflectionEnum;
use UnitEnum;

/**
 * @template T
 */
final class Option
{
    /** @var T */
    public mixed $displayValue {
        get {
            if (! ($this->value instanceof UnitEnum)) {
                return $this->value;
            }

            /** @phpstan-ignore-next-line */
            if (method_exists($this->value, 'toString')) {
                /** @phpstan-ignore-next-line */
                return $this->value->toString();
            }

            $reflection = new ReflectionEnum($this->value::class);

            /** @phpstan-ignore-next-line */
            if (! $reflection->isBacked()) {
                return $this->value->name;
            }

            return match ($reflection->getBackingType()->getName()) {
                /** @phpstan-ignore-next-line */
                'string' => $this->value->value,
                default => $this->value->name,
            };
        }
    }

    /** @param T $value */
    public function __construct(
        public int|string $key,
        public mixed $value,
    ) {
    }

    public function equals(Option $other): bool
    {
        return $other->key === $this->key && $other->value === $this->value;
    }
}
