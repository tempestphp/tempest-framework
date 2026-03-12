<?php

declare(strict_types=1);

namespace Tempest\DateTime;

/**
 * Specific formatting options for seconds. This may be extended in the future,
 * so exhaustive matching in external code is not recommended.
 *
 * @see TemporalInterface::toRfc3339()
 */
enum SecondsStyle: int
{
    /**
     * Format whole seconds only, with no decimal point.
     */
    case Seconds = 0;

    /**
     * Format 3 sub-second digits.
     */
    case Milliseconds = 3;

    /**
     * Format 6 sub-second digits.
     */
    case Microseconds = 6;

    /**
     * Format 9 sub-second digits.
     */
    case Nanoseconds = 9;

    public static function fromTimestamp(Timestamp $timestamp): SecondsStyle
    {
        $nanoseconds = $timestamp->getNanoseconds();

        return match (true) {
            $nanoseconds === 0 => static::Seconds,
            ($nanoseconds % 1000000) === 0 => static::Milliseconds,
            ($nanoseconds % 1000) === 0 => static::Microseconds,
            default => static::Nanoseconds,
        };
    }
}
