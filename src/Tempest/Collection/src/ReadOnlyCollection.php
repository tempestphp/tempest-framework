<?php

declare(strict_types=1);

namespace Tempest\Collection;

use LogicException;

/**
 * @template TValue
 */
trait ReadOnlyCollection
{
    /**
     * @param TValue $value
     */
    public function offsetSet(mixed $offset, $value): void
    {
        throw new LogicException('Cannot modify a read-only collection.');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new LogicException('Cannot modify a read-only collection.');
    }

    abstract public function offsetGet(mixed $offset): mixed;

    abstract public function offsetExists(mixed $offset): bool;
}
