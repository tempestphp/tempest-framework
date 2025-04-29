<?php

declare(strict_types=1);

namespace Tempest\DateTime;

/**
 * Represents the two halves of the day in a 12-hour clock format as an enum.
 *
 * This enum distinguishes between the ante meridiem (AM) period, before midday,
 * and the post meridiem (PM) period, after midday. It provides a type-safe way to represent
 * and work with these two divisions of the day.
 */
enum Meridiem: string
{
    case ANTE_MERIDIEM = 'AM';
    case POST_MERIDIEM = 'PM';

    /**
     * Creates a Meridiem instance based on the given hour in a 24-hour time format.
     *
     * @param int<0, 23> $hour The hour in a 24-hour format.
     *
     * @return Meridiem Returns AnteMeridiem for hours less than 12, and PostMeridiem for hours 12 and above.
     */
    public static function fromHour(int $hour): Meridiem
    {
        return $hour < 12 ? self::ANTE_MERIDIEM : self::POST_MERIDIEM;
    }

    /**
     * Toggles between AnteMeridiem (AM) and PostMeridiem (PM).
     *
     * @return Meridiem Returns PostMeridiem if the current instance is AnteMeridiem, and vice versa.
     */
    public function toggle(): Meridiem
    {
        return $this === self::ANTE_MERIDIEM ? self::POST_MERIDIEM : self::ANTE_MERIDIEM;
    }
}
