<?php

declare(strict_types=1);

namespace Tempest\Support\Arr;

use ArrayAccess;
use Countable;
use Iterator;

/**
 * @template TKey of array-key
 * @template TValue
 *
 * @extends ArrayAccess<TKey, TValue>
 * @extends Iterator<TKey, TValue>
 *
 * @internal This interface is not meant to be used in userland.
 */
interface ArrayInterface extends Iterator, ArrayAccess, Countable
{
    /**
     * Returns the underlying array of the instance.
     *
     * @return array<TKey,TValue>
     */
    public function toArray(): array;
}
