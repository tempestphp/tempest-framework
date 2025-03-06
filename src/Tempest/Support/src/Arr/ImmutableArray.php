<?php

declare(strict_types=1);

namespace Tempest\Support\Arr;

/**
 * @template TKey of array-key
 * @template TValue
 *
 * @implements ArrayInterface<TKey, TValue>
 */
final class ImmutableArray implements ArrayInterface
{
    use IsIterable;
    use ManipulatesArray;

    /**
     * Converts this instance to an {@see \Tempest\Support\Arr\MutableArray} instance.
     */
    public function toMutableArray(): MutableArray
    {
        return new MutableArray($this->value);
    }

    /**
     * Returns a new instance with the specified iterable,
     * or mutates the instance if this is a `MutableCollection`.
     */
    protected function createOrModify(iterable $array): self
    {
        return new self($array);
    }
}
