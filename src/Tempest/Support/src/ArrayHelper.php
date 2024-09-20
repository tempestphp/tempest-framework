<?php

declare(strict_types=1);

namespace Tempest\Support;

use ArrayAccess;
use Closure;
use Countable;
use Generator;
use Iterator;
use Serializable;

final class ArrayHelper implements Iterator, ArrayAccess, Serializable, Countable
{
    use IsIterable;

    private array $array;

    public function __construct(
        mixed $input = [],
    )
    {
        if (is_array($input)) {
            $this->array = $input;
        } elseif ($input instanceof self) {
            $this->array = $input->array;
        } else {
            $this->array = [$input];
        }
    }

    public function equals(array|self $other): bool
    {
        $other = is_array($other) ? $other : $other->array;

        return $this->array === $other;
    }

    /** @param Closure(mixed $value, mixed $key): bool $filter */
    public function first(?Closure $filter = null): mixed
    {
        if ($filter === null) {
            return $this->array[array_key_first($this->array)];
        }

        foreach ($this as $key => $value) {
            if ($filter($value, $key)) {
                return $value;
            }
        }

        return null;
    }

    /** @param Closure(mixed $value, mixed $key): bool $filter */
    public function last(?Closure $filter = null): mixed
    {
        if ($filter === null) {
            return $this->array[array_key_last($this->array)];
        }

        foreach ($this->reverse() as $key => $value) {
            if ($filter($value, $key)) {
                return $value;
            }
        }

        return null;
    }

    /** @param mixed $value The popped value will be stored in this variable */
    public function pop(mixed &$value): self
    {
        $value = $this->last();

        return new self(array_slice($this->array, 0, -1));
    }

    /** @param mixed $value The unshifted value will be stored in this variable */
    public function unshift(mixed &$value): self
    {
        $value = $this->first();

        return new self(array_slice($this->array, 1));
    }

    public function reverse(): self
    {
        return new self(array_reverse($this->array));
    }

    public function isEmpty(): bool
    {
        return empty($this->array);
    }

    public function isNotEmpty(): bool
    {
        return ! $this->isEmpty();
    }

    public function implode(string $glue): string
    {
        return implode($glue, $this->array);
    }

    public function values(): self
    {
        return new self(array_values($this->array));
    }

    /** @param Closure(mixed $value, mixed $key): bool $filter */
    public function filter(Closure $filter): self
    {
        $array = [];

        foreach ($this as $key => $value) {
            if ($filter($value, $key)) {
                $array[$key] = $value;
            }
        }

        return new self($array);
    }

    /** @param Closure(mixed $value, mixed $key): void $each */
    public function each(Closure $each): self
    {
        foreach ($this as $key => $value) {
            $each($value, $key);
        }

        return $this;
    }

    /** @param Closure(mixed $value, mixed $key): mixed $map */
    public function map(Closure $map): self
    {
        $array = [];

        foreach ($this->array as $key => $value) {
            $array[$key] = $map($value, $key);
        }

        return new self($array);
    }

    /** @param Closure(mixed $value, mixed $key): Generator $map */
    public function mapWithKeys(Closure $map): self
    {
        $array = [];

        foreach ($this->array as $key => $value) {
            $generator = $map($value, $key);

            if (! $generator instanceof Generator) {
                throw new InvalidMapWithKeysUsage();
            }

            $array[$generator->key()] = $generator->current();
        }

        return new self($array);
    }

    /** @return mixed|ArrayHelper */
    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->array;

        $keys = explode('.', $key);

        foreach ($keys as $key) {
            if (! isset($value[$key])) {
                return $default;
            }

            $value = $value[$key];
        }

        if (is_array($value)) {
            return new self($value);
        }

        return $value;
    }

    public function has(string $key): bool
    {
        $array = $this->array;

        $keys = explode('.', $key);

        foreach ($keys as $key) {
            if (! isset($array[$key])) {
                return false;
            }

            $array = &$array[$key];
        }

        return true;
    }

    public function set(string $key, mixed $value): self
    {
        $array = $this->array;

        $current = &$array;

        $keys = explode('.', $key);

        foreach ($keys as $i => $key) {
            // If this is the last key in dot notation, we don't
            // need to go through the next steps.
            if (count($keys) === 1) {
                break;
            }

            // Remove the current key from our keys array
            // so that later we can use the first value
            // from that array as our key.
            unset($keys[$i]);

            // If we know this key is not an array, make it one.
            if (! isset($current[$key]) || ! is_array($current[$key])) {
                $current[$key] = [];
            }

            // Set the context to this key.
            $current = &$current[$key];
        }

        // Pull the first key out of the array
        // and use it to set the value.
        $current[array_shift($keys)] = $value;

        return new self($array);
    }

    public function unwrap(): self
    {
        $unwrapValue = function (string|int $key, mixed $value) {
            if (is_int($key)) {
                return [$key => $value];
            }

            $keys = explode('.', $key);

            for ($i = array_key_last($keys); $i >= 0; $i--) {
                $currentKey = $keys[$i];

                $value = [$currentKey => $value];
            }

            return $value;
        };

        $array = [];

        foreach ($this->array as $key => $value) {
            $array = array_merge_recursive($array, $unwrapValue($key, $value));
        }

        return new self($array);
    }

    public function toArray(): array
    {
        return $this->array;
    }
}
