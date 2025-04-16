<?php

declare(strict_types=1);

namespace Tempest\DateTime;

/**
 * Represents the months of the year as an enum.
 *
 * This enum provides a type-safe way to work with months in a year, offering methods to
 * get the previous and next month, as well as determining the number of days in a month for a given year,
 * considering leap years. Each case in the enum corresponds to a month, represented by an integer
 * starting with January as 1 through December as 12.
 */
enum Month: int
{
    case JANUARY = 1;
    case FEBRUARY = 2;
    case MARCH = 3;
    case APRIL = 4;
    case MAY = 5;
    case JUNE = 6;
    case JULY = 7;
    case AUGUST = 8;
    case SEPTEMBER = 9;
    case OCTOBER = 10;
    case NOVEMBER = 11;
    case DECEMBER = 12;

    /**
     * Returns the previous month.
     *
     * This method calculates and returns the month preceding the current instance of the Month enum.
     *
     * If the current instance is January, it wraps around and returns December.
     *
     * @return Month The previous month.
     */
    public function getPrevious(): Month
    {
        return match ($this) {
            self::JANUARY => self::DECEMBER,
            self::FEBRUARY => self::JANUARY,
            self::MARCH => self::FEBRUARY,
            self::APRIL => self::MARCH,
            self::MAY => self::APRIL,
            self::JUNE => self::MAY,
            self::JULY => self::JUNE,
            self::AUGUST => self::JULY,
            self::SEPTEMBER => self::AUGUST,
            self::OCTOBER => self::SEPTEMBER,
            self::NOVEMBER => self::OCTOBER,
            self::DECEMBER => self::NOVEMBER,
        };
    }

    /**
     * Returns the next month.
     *
     * This method calculates and returns the month succeeding the current instance of the Month enum.
     *
     * If the current instance is December, it wraps around and returns January.
     *
     * @return Month The next month.
     */
    public function getNext(): Month
    {
        return match ($this) {
            self::JANUARY => self::FEBRUARY,
            self::FEBRUARY => self::MARCH,
            self::MARCH => self::APRIL,
            self::APRIL => self::MAY,
            self::MAY => self::JUNE,
            self::JUNE => self::JULY,
            self::JULY => self::AUGUST,
            self::AUGUST => self::SEPTEMBER,
            self::SEPTEMBER => self::OCTOBER,
            self::OCTOBER => self::NOVEMBER,
            self::NOVEMBER => self::DECEMBER,
            self::DECEMBER => self::JANUARY,
        };
    }

    /**
     * Returns the number of days in the month for a given year.
     *
     * This method determines the number of days in the current month instance, considering whether the
     * provided year is a leap year or not. It uses separate methods for leap years and non-leap years
     * to get the appropriate day count.
     *
     * @param int $year The year for which the day count is needed.
     *
     * @return int<28, 31> The number of days in the month for the specified year.
     */
    public function getDaysForYear(int $year): int
    {
        if (namespace\is_leap_year($year)) {
            return $this->getLeapYearDays();
        }

        return $this->getNonLeapYearDays();
    }

    /**
     * Returns the number of days in the month for a non-leap year.
     *
     * This method provides the standard day count for the current month instance in a non-leap year.
     *
     * February returns 28, while April, June, September, and November return 30, and the rest return 31.
     *
     * @return int<28, 31> The number of days in the month for a non-leap year.
     */
    public function getNonLeapYearDays(): int
    {
        return match ($this) {
            self::JANUARY, self::MARCH, self::MAY, self::JULY, self::AUGUST, self::OCTOBER, self::DECEMBER => 31,
            self::FEBRUARY => 28,
            self::APRIL, self::JUNE, self::SEPTEMBER, self::NOVEMBER => 30,
        };
    }

    /**
     * Returns the number of days in the month for a leap year.
     *
     * This method provides the day count for the current month instance in a leap year.
     *
     * February returns 29, while April, June, September, and November return 30, and the rest return 31.
     *
     * @return int<29, 31> The number of days in the month for a leap year.
     */
    public function getLeapYearDays(): int
    {
        return match ($this) {
            self::JANUARY, self::MARCH, self::MAY, self::JULY, self::AUGUST, self::OCTOBER, self::DECEMBER => 31,
            self::FEBRUARY => 29,
            self::APRIL, self::JUNE, self::SEPTEMBER, self::NOVEMBER => 30,
        };
    }
}
