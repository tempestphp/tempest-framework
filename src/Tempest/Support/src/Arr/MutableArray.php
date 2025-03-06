<?php

declare(strict_types=1);

namespace Tempest\Support\Arr;

/**
 * @template TKey of array-key
 * @template TValue
 *
 * @implements ArrayInterface<TKey, TValue>
 */
final class MutableArray implements ArrayInterface
{
    use IsIterable;
    use ManipulatesArray;

    /**
     * Converts this instance to an {@see \Tempest\Support\Arr\ImmutableArray} instance.
     */
    public function toImmutableArray(): ImmutableArray
    {
        return new ImmutableArray($this->value);
    }

    /**
     * Gets a value from the array and remove it.
     *
     * @param array-key $key
     */
    public function pull(string|int $key, mixed $default = null): mixed
    {
        return namespace\pull($this->value, $key, $default);
    }

    /**
     * Returns a new instance with the specified iterable,
     * or mutates the instance if this is a `MutableCollection`.
     */
    protected function createOrModify(iterable $array): self
    {
        $this->value = iterator_to_array($array);

        return $this;
    }
}
