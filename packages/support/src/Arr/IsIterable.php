<?php

declare(strict_types=1);

namespace Tempest\Support\Arr;

/** @internal */
trait IsIterable
{
    public function current(): mixed
    {
        return current($this->value);
    }

    public function next(): void
    {
        next($this->value);
    }

    public function key(): string|int|null
    {
        return key($this->value);
    }

    public function valid(): bool
    {
        return $this->current() !== false;
    }

    public function rewind(): void
    {
        reset($this->value);
    }

    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->value);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->value[$offset] ?? throw new OffsetDidNotExist();
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset === null) {
            $this->value[] = $value;
        } else {
            $this->value[$offset] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->value[$offset]);
    }

    public function serialize(): string
    {
        return serialize($this->value);
    }

    public function unserialize(string $data): void
    {
        $this->value = unserialize($data);
    }

    public function __serialize(): array
    {
        return [
            'array' => $this->value,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->value = $data['array'];
    }

    public function count(): int
    {
        return count($this->value);
    }
}
