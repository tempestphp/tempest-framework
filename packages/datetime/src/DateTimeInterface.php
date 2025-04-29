<?php

declare(strict_types=1);

namespace Tempest\DateTime;

use Tempest\Support\Language\Locale;

interface DateTimeInterface extends TemporalInterface
{
    /**
     * Creates a {@see DateTimeInterface} instance from a timestamp, representing the same point in time.
     *
     * This method converts a {@see Timestamp} into a {@see DateTimeInterface} instance calculated for the specified timezone.
     *
     * @param null|Timezone $timezone Optional timezone. If null, uses the system's default timezone.
     *
     * @see Timezone::default()
     */
    public static function fromTimestamp(Timestamp $timestamp, ?Timezone $timezone = null): static;

    /**
     * Checks if this {@see DateTimeInterface} instance is equal to the given {@see DateTimeInterface} instance including the timezone.
     *
     * @param DateTimeInterface $other The {@see DateTimeInterface} instance to compare with.
     *
     * @return bool True if equal including timezone, false otherwise.
     */
    public function equalsIncludingTimezone(DateTimeInterface $other): bool;

    /**
     * Gets the timezone associated with this {@see DateTimeInterface} instance.
     */
    public function getTimezone(): Timezone;

    /**
     * Obtains the timezone offset as a {@see Duration} object.
     *
     * This method effectively returns the offset from UTC for the timezone of this instance at the specific date and time it represents.
     *
     * It is equivalent to executing `$dt->getTimezone()->getOffset($dt)`, which calculates the offset for the timezone of this instance.
     *
     * @return Duration The offset from UTC as a Duration.
     */
    public function getTimezoneOffset(): Duration;

    /**
     * Determines whether this instance is currently in daylight saving time.
     *
     * This method checks if the date and time represented by this instance fall within the daylight saving time period of its timezone.
     *
     * It is equivalent to `!$dt->getTimezone()->getDaylightSavingTimeOffset($dt)->isZero()`, indicating whether there is a non-zero DST offset.
     *
     * @return bool True if in daylight saving time, false otherwise.
     */
    public function isDaylightSavingTime(): bool;

    /**
     * Returns a new instance with the specified date.
     *
     * @param Month|int<1, 12> $month
     * @param int<1, 31> $day
     *
     * @throws Exception\UnexpectedValueException If any of the provided date components do not align with calendar expectations.
     */
    public function withDate(int $year, Month|int $month, int $day): static;

    /**
     * Returns a new instance with the specified time.
     *
     * @param int<0, 23> $hours
     * @param int<0, 59> $minutes
     * @param int<0, 59> $seconds
     * @param int<0, 999999999> $nanoseconds
     *
     * @throws Exception\UnexpectedValueException If any of the provided time components do not align with calendar expectations.
     */
    public function withTime(int $hours, int $minutes, int $seconds = 0, int $nanoseconds = 0): static;

    /**
     * Returns a new instance with the specified year.
     *
     * @throws Exception\UnexpectedValueException If the provided year do not align with calendar expectations.
     */
    public function withYear(int $year): static;

    /**
     * Returns a new instance with the specified month.
     *
     * @param Month|int<1, 12> $month
     *
     * @throws Exception\UnexpectedValueException If the provided month do not align with calendar expectations.
     */
    public function withMonth(Month|int $month): static;

    /**
     * Returns a new instance with the specified day.
     *
     * @param int<1, 31> $day
     *
     * @throws Exception\UnexpectedValueException If the provided day do not align with calendar expectations.
     */
    public function withDay(int $day): static;

    /**
     * Returns a new instance with the specified hours.
     *
     * @param int<0, 23> $hours
     *
     * @throws Exception\UnexpectedValueException If the provided hours do not align with calendar expectations.
     */
    public function withHours(int $hours): static;

    /**
     * Returns a new instance with the specified minutes.
     *
     * @param int<0, 59> $minutes
     *
     * @throws Exception\UnexpectedValueException If the provided minutes do not align with calendar expectations.
     */
    public function withMinutes(int $minutes): static;

    /**
     * Returns a new instance with the specified seconds.
     *
     * @param int<0, 59> $seconds
     *
     * @throws Exception\UnexpectedValueException If the provided seconds do not align with calendar expectations.
     */
    public function withSeconds(int $seconds): static;

    /**
     * Returns a new instance with the specified nanoseconds.
     *
     * @param int<0, 999999999> $nanoseconds
     *
     * @throws Exception\UnexpectedValueException If the provided nanoseconds do not align with calendar expectations.
     */
    public function withNanoseconds(int $nanoseconds): static;

    /**
     * Returns the date (year, month, day).
     *
     * @return array{int, int<1, 12>, int<1, 31>} The date.
     */
    public function getDate(): array;

    /**
     * Returns the time (hours, minutes, seconds, nanoseconds).
     *
     * @return array{
     *     int<0, 23>,
     *     int<0, 59>,
     *     int<0, 59>,
     *     int<0, 999999999>,
     * }
     */
    public function getTime(): array;

    /**
     * Returns the date and time parts (year, month, day, hours, minutes, seconds, nanoseconds).
     *
     * @return array{
     *     int,
     *     int<1, 12>,
     *     int<1, 31>,
     *     int<0, 23>,
     *     int<0, 59>,
     *     int<0, 59>,
     *     int<0, 999999999>,
     * }
     */
    public function getParts(): array;

    /**
     * Retrieves the era of the date represented by this DateTime instance.
     *
     * This method returns an instance of the `Era` enum, which indicates whether the date
     * falls in the Anno Domini (AD) or Before Christ (BC) era. The era is determined based on the year
     * of the date this object represents, with years designated as BC being negative
     * and years in AD being positive.
     */
    public function getEra(): Era;

    /**
     * Returns the century number for the year stored in this object.
     */
    public function getCentury(): int;

    /**
     * Retrieves the year as an integer, following ISO-8601 conventions for numbering.
     *
     * This method returns the year part of the date. For years in the Anno Domini (AD) era, the returned value matches
     * the Gregorian calendar year directly (e.g., 1 for AD 1, 2021 for AD 2021, etc.). For years before AD 1, the method
     * adheres to the ISO-8601 standard, which does not use a year zero: 1 BC is represented as 0, 2 BC as -1, 3 BC as -2,
     * and so forth. This ISO-8601 numbering facilitates straightforward mathematical operations on years across the AD/BC
     * divide but may require conversion for user-friendly display or when interfacing with systems that use the traditional
     * AD/BC notation.
     *
     * @return int The year, formatted according to ISO-8601 standards, where 1 AD is 1, 1 BC is 0, 2 BC is -1, etc.
     */
    public function getYear(): int;

    /**
     * Returns the short format of the year (last 2 digits).
     *
     * @return int<-99, 99> The short format of the year.
     */
    public function getYearShort(): int;

    /**
     * Returns the month.
     *
     * @return int<1, 12>
     */
    public function getMonth(): int;

    /**
     * Returns the month as an instance of the {@see Month} enum.
     *
     * This method converts the numeric representation of the month into its corresponding
     * case in the {@see Month} enum, providing a type-safe way to work with months.
     *
     * @return Month The month as an enum case.
     */
    public function getMonthEnum(): Month;

    /**
     * Returns the day.
     *
     * @return int<0, 31>
     */
    public function getDay(): int;

    /**
     * Returns the hours.
     *
     * @return int<0, 23>
     */
    public function getHours(): int;

    /**
     * Returns the hours using the 12-hour format (1 to 12) along with the meridiem indicator.
     *
     * @return array{int<1, 12>, Meridiem} The hours and meridiem indicator.
     */
    public function getTwelveHours(): array;

    /**
     * Returns the minutes.
     *
     * @return int<0, 59>
     */
    public function getMinutes(): int;

    /**
     * Returns the seconds.
     *
     * @return int<0, 59>
     */
    public function getSeconds(): int;

    /**
     * Returns the nanoseconds.
     *
     * @return int<0, 999999999>
     */
    public function getNanoseconds(): int;

    /**
     * Retrieves the ISO-8601 year and week number corresponding to the date.
     *
     * This method returns an array consisting of two integers: the first represents the year, and the second
     * represents the week number according to ISO-8601 standards, which ranges from 1 to 53. The week numbering
     * follows the ISO-8601 specification, where a week starts on a Monday and the first week of the year is the
     * one that contains at least four days of the new year.
     *
     * Due to the ISO-8601 week numbering rules, the returned year might not always match the Gregorian year
     * obtained from `$this->getYear()`. Specifically:
     *
     *  - The first few days of January might belong to the last week of the previous year if they fall before
     *      the first Thursday of January.
     *
     *  - Conversely, the last days of December might be part of the first week of the following year if they
     *      extend beyond the last Thursday of December.
     *
     * Examples:
     *  - For the date 2020-01-01, it returns [2020, 1], indicating the first week of 2020.
     *  - For the date 2021-01-01, it returns [2020, 53], showing that this day is part of the last week of 2020
     *      according to ISO-8601.
     *
     * @return array{int, int<1, 53>}
     */
    public function getISOWeekNumber(): array;

    /**
     * Gets the weekday of the date.
     *
     * @return Weekday The weekday.
     */
    public function getWeekday(): Weekday;

    /**
     * Checks if the year is a leap year.
     */
    public function isLeapYear(): bool;

    /**
     * Adds the specified years to this date-time object, returning a new instance with the added years.
     *
     * @throws Exception\UnderflowException If adding the years results in an arithmetic underflow.
     * @throws Exception\OverflowException If adding the years results in an arithmetic overflow.
     */
    public function plusYears(int $years): static;

    /**
     * Adds the specified months to this date-time object, returning a new instance with the added months.
     *
     * @throws Exception\UnderflowException If adding the months results in an arithmetic underflow.
     * @throws Exception\OverflowException If adding the months results in an arithmetic overflow.
     */
    public function plusMonths(int $months): static;

    /**
     * Adds the specified days to this date-time object, returning a new instance with the added days.
     *
     * @throws Exception\UnderflowException If adding the days results in an arithmetic underflow.
     * @throws Exception\OverflowException If adding the days results in an arithmetic overflow.
     */
    public function plusDays(int $days): static;

    /**
     * Subtracts the specified years from this date-time object, returning a new instance with the subtracted years.
     *
     * @throws Exception\UnderflowException If subtracting the years results in an arithmetic underflow.
     * @throws Exception\OverflowException If subtracting the years results in an arithmetic overflow.
     */
    public function minusYears(int $years): static;

    /**
     * Subtracts the specified months from this date-time object, returning a new instance with the subtracted months.
     *
     * @throws Exception\UnderflowException If subtracting the months results in an arithmetic underflow.
     * @throws Exception\OverflowException If subtracting the months results in an arithmetic overflow.
     */
    public function minusMonths(int $months): static;

    /**
     * Subtracts the specified days from this date-time object, returning a new instance with the subtracted days.
     *
     * @throws Exception\UnderflowException If subtracting the days results in an arithmetic underflow.
     * @throws Exception\OverflowException If subtracting the days results in an arithmetic overflow.
     */
    public function minusDays(int $days): static;

    /**
     * Formats this {@see DateTimeInterface} instance based on a specific pattern, with optional customization for timezone and locale.
     *
     * This method allows for detailed customization of the output string by specifying a format pattern. If no pattern is provided,
     * a default, implementation-specific pattern will be used. Additionally, the method supports specifying a timezone and locale
     * for further customization of the formatted output. If these are not provided, system defaults will be used.
     *
     * Example usage:
     *
     * ```php
     * $formatted = $temporal->format('yyyy-MM-dd HH:mm:ss', $timezone, $locale);
     * ```
     *
     * @param null|FormatPattern|string $pattern Optional custom format pattern for the date and time. If null, uses a default pattern.
     * @param null|Timezone $timezone Optional timezone for formatting. If null, uses the current timezone.
     * @param null|Locale $locale Optional locale for formatting. If null, uses the system's default locale.
     *
     * @return string The formatted date and time string, according to the specified pattern, timezone, and locale.
     *
     * @see https://unicode-org.github.io/icu/userguide/format_parse/datetime/#datetime-format-syntax
     * @see Locale::default()
     */
    #[\Override]
    public function format(null|FormatPattern|string $pattern = null, ?Timezone $timezone = null, ?Locale $locale = null): string;

    /**
     * Provides a string representation of this {@see TemporalInterface} instance, formatted according to specified styles for date and time,
     * and optionally adjusted for a specific timezone and locale.
     *
     * This method offers a higher-level abstraction for formatting, allowing users to specify styles for date and time separately
     * rather than a custom pattern. If no styles are provided, default styles will be used.
     *
     * Additionally, the timezone and locale can be specified for locale-sensitive formatting.
     *
     * Example usage:
     *
     * ```php
     * $string_representation = $temporal->toString(FormatDateStyle::Long, FormatTimeStyle::Short, $timezone, $locale);
     * ```
     *
     * @param null|DateStyle $dateStyle Optional style for the date portion of the output. If null, a default style is used.
     * @param null|TimeStyle $timeStyle Optional style for the time portion of the output. If null, a default style is used.
     * @param null|Timezone $timezone Optional timezone for formatting. If null, uses the current timezone.
     * @param null|Locale $locale Optional locale for formatting. If null, uses the system's default locale.
     *
     * @return string The string representation of the date and time, formatted according to the specified styles, timezone, and locale.
     *
     * @see DateStyle::default()
     * @see TimeStyle::default()
     * @see Locale::default()
     */
    #[\Override]
    public function toString(?DateStyle $dateStyle = null, ?TimeStyle $timeStyle = null, ?Timezone $timezone = null, ?Locale $locale = null): string;

    /**
     * Formats this {@see DateTimeInterface} instance to a string based on the RFC 3339 format, with additional
     * options for second fractions and timezone representation.
     *
     * The RFC 3339 format is widely adopted in web and network protocols for its unambiguous representation of date, time,
     * and timezone information. This method not only ensures universal readability but also the precise specification
     * of time across various systems, being compliant with both RFC 3339 and ISO 8601 standards.
     *
     * Example usage:
     *
     * ```php
     * // Default formatting
     * $rfc_formatted_string = $datetime->toRfc3339();
     * // Customized formatting with milliseconds and 'Z' for UTC
     * $rfc_formatted_string_with_milliseconds_and_z = $datetime->toRfc3339(SecondsStyle::Milliseconds, true);
     * ```
     *
     * @param null|SecondsStyle $secondsStyle Optional parameter to specify the seconds formatting style. Automatically
     *                                         selected based on precision if null.
     * @param bool $useZ Determines the representation of UTC timezone. True to use 'Z', false to use the standard offset format.
     *
     * @return string The formatted string of the {@see DateTimeInterface} instance, adhering to the RFC 3339 and compatible with ISO 8601 formats.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc3339
     */
    #[\Override]
    public function toRfc3339(?SecondsStyle $secondsStyle = null, bool $useZ = false): string;

    /**
     * Magic method that provides a default string representation of the date and time.
     *
     * This method is a shortcut for calling `toString()` with all null arguments, returning a string formatted
     * with default styles, timezone, and locale. It is automatically called when the object is used in a string context.
     *
     * Example usage:
     *
     * ```php
     * $default_string_representation = (string) $temporal; // Uses __toString() for formatting
     * ```
     *
     * @return string The default string representation of the date and time.
     *
     * @see TemporalInterface::toString()
     */
    #[\Override]
    public function __toString(): string;

    /**
     * Converts the {@see DateTimeInterface} instance to the specified timezone.
     *
     * @param null|Timezone $timezone The timezone to convert to.
     */
    #[\Override]
    public function convertToTimezone(?Timezone $timezone): static;
}
