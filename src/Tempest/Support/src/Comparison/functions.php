<?php

namespace Tempest\Support\Comparison {
    /**
     * @template T
     *
     * @param T $a
     * @param T $b
     *
     * This method can be used as a sorter callback function for Comparable items.
     *
     * Vec\sort($list, Comparable\sort(...))
     */
    function sort(mixed $a, mixed $b): int
    {
        return compare($a, $b)->value;
    }

    /**
     * @template T
     *
     * @param T $a
     * @param T $b
     */
    function not_equal(mixed $a, mixed $b): bool
    {
        return compare($a, $b) !== Order::EQUAL;
    }

    /**
     * @template T
     *
     * @param T $a
     * @param T $b
     */
    function less(mixed $a, mixed $b): bool
    {
        return compare($a, $b) === Order::LESS;
    }

    /**
     * @template T
     *
     * @param T $a
     * @param T $b
     */
    function less_or_equal(mixed $a, mixed $b): bool
    {
        $order = compare($a, $b);

        return $order === Order::EQUAL || $order === Order::LESS;
    }

    /**
     * @template T
     *
     * @param T $a
     * @param T $b
     */
    function greater(mixed $a, mixed $b): bool
    {
        return compare($a, $b) === Order::GREATER;
    }

    /**
     * @template T
     *
     * @param T $a
     * @param T $b
     */
    function greater_or_equal(mixed $a, mixed $b): bool
    {
        $order = compare($a, $b);

        return $order === Order::EQUAL || $order === Order::GREATER;
    }

    /**
     * @template T
     *
     * @param T $a
     * @param T $b
     */
    function equal(mixed $a, mixed $b): bool
    {
        return compare($a, $b) === Order::EQUAL;
    }

    /**
     * @template T
     *
     * @param T $a
     * @param T $b
     *
     * This function can compare 2 values of a similar type.
     * When the type happens to be mixed or never, it will fall back to PHP's internal comparison rules:
     *
     * @link https://www.php.net/manual/en/language.operators.comparison.php
     * @link https://www.php.net/manual/en/types.comparisons.php
     */
    function compare(mixed $a, mixed $b): Order
    {
        if ($a instanceof Comparable) {
            return $a->compare($b);
        }

        return Order::from($a <=> $b);
    }
}
