<?php

declare(strict_types=1);

namespace Tempest\Collection;

use ArrayAccess;
use Iterator;

/**
 * @template TKey of int|string
 * @template TValue
 *
 * @extends ArrayAccess<TKey,TValue>
 * @extends Iterator<TKey,TValue>
 */
interface Collection extends ArrayAccess, Iterator
{
    /**
     * Get the item at the referenced key.
     *
     * @param TKey $key
     * @return TValue
     */
    public function get(mixed $key): mixed;

    /**
     * Determined if the specified key exists.
     *
     * @param TKey $key
     * @return bool
     */
    public function has(mixed $key): bool;

    /**
     * Adds a value to an ordered list.
     *
     * @param TValue $item
     * @return self<TKey,TValue>
     */
    public function add(mixed $item): self;

    /**
     * Sets the value at a particular key in an unordered list or updates
     * the existing value of a key in an ordered list.
     *
     * @param TKey $key
     * @param TValue $value
     * @return self<TKey,TValue>
     */
    public function set(mixed $key, mixed $value): self;

    /**
     * Removes the specified value from the list.
     *
     * @param TValue $item
     * @return self<TKey,TValue>
     */
    public function remove(mixed $item): self;

    /**
     * Removes the specified key from the list without changing the order.
     *
     * @param TKey $key
     * @return self<TKey,TValue>
     */
    public function removeAt(mixed $key): self;

    /**
     * @param TValue $item
     * @return TKey
     */
    public function indexOf(mixed $item): mixed;
}
