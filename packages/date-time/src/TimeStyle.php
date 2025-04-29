<?php

declare(strict_types=1);

namespace Tempest\DateTime;

/**
 * Enumeration representing time format styles.
 *
 * Similar to the DateFormatStyle, this enum specifies the style for time formatting operations, defining the level of detail
 * to be included in time representations. The styles allow for a range from no time (NONE) to full detail (FULL).
 *
 * The default format style is Medium.
 */
enum TimeStyle
{
    /**
     * No time formatting.
     */
    case NONE;

    /**
     * Short format, typically for hour and minute (e.g., 5:30 PM).
     */
    case SHORT;

    /**
     * Medium format, which may include hour, minute, and second (e.g., 5:30:00 PM).
     */
    case MEDIUM;

    /**
     * Long format, providing detailed time information often including time zone (e.g., 5:30:00 PM PST).
     */
    case LONG;

    /**
     * Full format, offering the most detailed time representation, including hour, minute, second, and time zone (e.g., 5:30:00 PM Pacific Standard Time).
     */
    case FULL;

    /**
     * Returns the default time format style.
     *
     * Implements the DefaultInterface to standardize access to the default enum case for time formatting.
     * The Medium style is the default, including hour, minute, and potentially seconds for a balance of detail.
     *
     * @return static The default time format style.
     */
    public static function default(): static
    {
        return self::MEDIUM;
    }
}
