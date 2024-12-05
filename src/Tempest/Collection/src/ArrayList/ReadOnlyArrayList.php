<?php

declare(strict_types=1);

namespace Tempest\Collection\ArrayList;

use ArrayAccess;
use Tempest\Collection\ReadOnlyCollection;

/**
 * @template TValue
 */
final class ReadOnlyArrayList implements ArrayAccess
{
    /** @use ReadOnlyCollection<TValue> */
    use ReadOnlyCollection;

    /** @use ReadsArrayList<TValue> */
    use ReadsArrayList;
}
