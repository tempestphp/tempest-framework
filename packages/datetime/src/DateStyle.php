<?php

declare(strict_types=1);

namespace Tempest\DateTime;

/**
 * Enumeration representing date format styles.
 *
 * This enum is used to specify the style of date formatting operations, allowing for varying levels of detail.
 * The styles range from no date (NONE) to a fully detailed date representation (FULL).
 *
 * The default format style is MEDIUM.
 */
enum DateStyle
{
    /**
     * No date formatting
     */
    case NONE;

    /**
     * Short format that typically includes numeric values (e.g., 12/31/2020).
     */
    case SHORT;

    /**
     * Medium format that provides a balance between brevity and detail (e.g., Jan 31, 2020).
     */
    case MEDIUM;

    /**
     * Long format that includes full month names and often includes the day of the week (e.g., Friday, January 31, 2020).
     */
    case LONG;

    /**
     * Full format that provides the most detail, often including the full day and month names, and the year in full (e.g., Friday, January 31, 2020).
     */
    case FULL;

    /**
     * The same as SHORT, but yesterday, today, and tomorrow show as yesterday, today, and tomorrow, respectively.
     */
    case RELATIVE_SHORT;

    /**
     * The same as MEDIUM, but yesterday, today, and tomorrow show as yesterday, today, and tomorrow, respectively.
     */
    case RELATIVE_MEDIUM;

    /**
     * The same as LONG, but yesterday, today, and tomorrow show as yesterday, today, and tomorrow, respectively.
     */
    case RELATIVE_LONG;

    /**
     * The same as FULL, but yesterday, today, and tomorrow show as yesterday, today, and tomorrow, respectively.
     */
    case RELATIVE_FULL;

    /**
     * Returns the default date format style.
     *
     * This method implements the DefaultInterface, providing a standard way to access the default enum case.
     * The Medium style is returned as the default, representing a balance between detail and brevity.
     *
     * @return static The default date format style.
     */
    public static function default(): static
    {
        return self::MEDIUM;
    }
}
