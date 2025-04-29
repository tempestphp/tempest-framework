<?php

declare(strict_types=1);

namespace Tempest\DateTime;

/**
 * Represents the days of the week as an enum.
 *
 * This enum provides a type-safe way to work with weekdays, offering methods to
 * get the previous and next day. Each case in the enum corresponds to a day,
 * represented by an integer according to the ISO-8601 standard, starting with
 * Monday as 1 through Sunday as 7.
 */
enum Weekday: int
{
    case MONDAY = 1;
    case TUESDAY = 2;
    case WEDNESDAY = 3;
    case THURSDAY = 4;
    case FRIDAY = 5;
    case SATURDAY = 6;
    case SUNDAY = 7;

    /**
     * Returns the previous weekday.
     *
     * If the current instance is Monday, it wraps around and returns Sunday.
     *
     * @return Weekday The previous weekday.
     */
    public function getPrevious(): Weekday
    {
        return match ($this) {
            self::MONDAY => self::SUNDAY,
            self::TUESDAY => self::MONDAY,
            self::WEDNESDAY => self::TUESDAY,
            self::THURSDAY => self::WEDNESDAY,
            self::FRIDAY => self::THURSDAY,
            self::SATURDAY => self::FRIDAY,
            self::SUNDAY => self::SATURDAY,
        };
    }

    /**
     * Returns the next weekday.
     *
     * If the current instance is Sunday, it wraps around and returns Monday.
     *
     * @return Weekday The next weekday.
     */
    public function getNext(): Weekday
    {
        return match ($this) {
            self::MONDAY => self::TUESDAY,
            self::TUESDAY => self::WEDNESDAY,
            self::WEDNESDAY => self::THURSDAY,
            self::THURSDAY => self::FRIDAY,
            self::FRIDAY => self::SATURDAY,
            self::SATURDAY => self::SUNDAY,
            self::SUNDAY => self::MONDAY,
        };
    }
}
