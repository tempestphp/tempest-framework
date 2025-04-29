<?php

declare(strict_types=1);

namespace Tempest\Support\Comparison;

/**
 * @template T
 */
interface Comparable
{
    /**
     * @param T $other
     */
    public function compare(mixed $other): Order;
}
