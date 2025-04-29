<?php

declare(strict_types=1);

namespace Tempest\Support\Comparison;

/**
 * Enumerates the possible outcomes of a comparison operation.
 *
 * This enum provides a standardized way to represent the result of a comparison,
 * making it easier to understand and use comparison outcomes in a type-safe manner.
 * Implementing the DefaultInterface, it also provides a sensible default value.
 *
 * - `LESS` indicates that the first value is less than the second.
 * - `EQUAL` suggests that the two values are equal.
 * - `GREATER` means that the first value is greater than the second.
 *
 * Usage of this enum can help to avoid "magic numbers" in comparison logic and make
 * code more readable and maintainable.
 */
enum Order: int
{
    case LESS = -1;
    case EQUAL = 0;
    case GREATER = 1;

    /**
     * Provides the default comparison outcome.
     *
     * This method returns the `Equal` case as the default state, indicating no difference
     * between the compared values. It's useful in contexts where a neutral or "no change"
     * state is needed as the starting point.
     *
     * @return static The default instance of the enum, which is `Order::Equal`.
     */
    public static function default(): static
    {
        return self::EQUAL;
    }
}
