<?php

declare(strict_types=1);

namespace Tempest\Collection\ArrayList;

use ArrayAccess;

/**
 * @template TValue
 */
final class ArrayList implements ArrayAccess
{
    /** @use ReadsArrayList<TValue> */
    use ReadsArrayList;

    /** @use WritesArrayList<TValue> */
    use WritesArrayList;

    /**
     * @return ReadOnlyArrayList<TValue>
     */
    public function toReadOnly(): ReadOnlyArrayList
    {
        return new ReadOnlyArrayList($this->items);
    }
}
