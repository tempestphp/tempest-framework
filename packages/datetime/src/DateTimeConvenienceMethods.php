<?php

declare(strict_types=1);

namespace Tempest\DateTime;

use Tempest\Intl\Locale;
use Tempest\Support\Math;

/**
 * @require-implements DateTimeInterface
 */
trait DateTimeConvenienceMethods
{
    use TemporalConvenienceMethods {
        toRfc3339 as private toRfc3339Impl;
    }

    /**
     * Checks if this {@see DateTimeInterface} instance is equal to the given {@see DateTimeInterface} instance including the timezone.
     *
     * @param DateTimeInterface $other The {@see DateTimeInterface} instance to compare with.
     *
     * @return bool True if equal including timezone, false otherwise.
     */
    public function equalsIncludingTimezone(DateTimeInterface $other): bool
    {
        return $this->equals($other) && $this->getTimezone() === $other->getTimezone();
    }

    /**
     * Obtains the timezone offset as a {@see Duration} object.
     *
     * This method effectively returns the offset from UTC for the timezone of this instance at the specific date and time it represents.
     *
     * It is equivalent to executing `$dt->getTimezone()->getOffset($dt)`, which calculates the offset for the timezone of this instance.
     *
     * @return Duration The offset from UTC as a Duration.
     */
    public function getTimezoneOffset(): Duration
    {
        return $this->getTimezone()->getOffset($this);
    }

    /**
     * Determines whether this instance is currently in daylight saving time.
     *
     * This method checks if the date and time represented by this instance fall within the daylight saving time period of its timezone.
     *
     * It is equivalent to `!$dt->getTimezone()->getDaylightSavingTimeOffset($dt)->isZero()`, indicating whether there is a non-zero DST offset.
     *
     * @return bool True if in daylight saving time, false otherwise.
     */
    public function isDaylightSavingTime(): bool
    {
        return ! $this->getTimezone()->getDaylightSavingTimeOffset($this)->isZero();
    }

    /**
     * Converts the {@see DateTimeInterface} instance to the specified timezone.
     *
     * @param null|Timezone $timezone The timezone to convert to.
     */
    #[\Override]
    public function convertToTimezone(?Timezone $timezone): static
    {
        if ($timezone === null) {
            return $this;
        }

        return static::fromTimestamp($this->getTimestamp(), $timezone);
    }

    /**
     * Returns a new instance with the specified year.
     *
     * @throws Exception\UnexpectedValueException If the provided year do not align with calendar expectations.
     */
    public function withYear(int $year): static
    {
        return $this->withDate($year, $this->getMonth(), $this->getDay());
    }

    /**
     * Returns a new instance with the specified month.
     *
     * @param Month|int<1, 12> $month
     *
     * @throws Exception\UnexpectedValueException If the provided month do not align with calendar expectations.
     */
    public function withMonth(Month|int $month): static
    {
        return $this->withDate($this->getYear(), $month, $this->getDay());
    }

    /**
     * Returns a new instance with the specified day.
     *
     * @param int<1, 31> $day
     *
     * @throws Exception\UnexpectedValueException If the provided day do not align with calendar expectations.
     */
    public function withDay(int $day): static
    {
        return $this->withDate($this->getYear(), $this->getMonth(), $day);
    }

    /**
     * Returns a new instance with the specified hours.
     *
     * @param int<0, 23> $hours
     *
     * @throws Exception\UnexpectedValueException If the provided hours do not align with calendar expectations.
     */
    public function withHours(int $hours): static
    {
        return $this->withTime($hours, $this->getMinutes(), $this->getSeconds(), $this->getNanoseconds());
    }

    /**
     * Returns a new instance with the specified minutes.
     *
     * @param int<0, 59> $minutes
     *
     * @throws Exception\UnexpectedValueException If the provided minutes do not align with calendar expectations.
     */
    public function withMinutes(int $minutes): static
    {
        return $this->withTime($this->getHours(), $minutes, $this->getSeconds(), $this->getNanoseconds());
    }

    /**
     * Returns a new instance with the specified seconds.
     *
     * @param int<0, 59> $seconds
     *
     * @throws Exception\UnexpectedValueException If the provided seconds do not align with calendar expectations.
     */
    public function withSeconds(int $seconds): static
    {
        return $this->withTime($this->getHours(), $this->getMinutes(), $seconds, $this->getNanoseconds());
    }

    /**
     * Returns a new instance with the specified nanoseconds.
     *
     * @param int<0, 999999999> $nanoseconds
     *
     * @throws Exception\UnexpectedValueException If the provided nanoseconds do not align with calendar expectations.
     */
    public function withNanoseconds(int $nanoseconds): static
    {
        return $this->withTime($this->getHours(), $this->getMinutes(), $this->getSeconds(), $nanoseconds);
    }

    /**
     * Returns the date (year, month, day).
     *
     * @return array{int, int<1, 12>, int<1, 31>} The date.
     */
    public function getDate(): array
    {
        return [$this->getYear(), $this->getMonth(), $this->getDay()];
    }

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
    public function getTime(): array
    {
        return [
            $this->getHours(),
            $this->getMinutes(),
            $this->getSeconds(),
            $this->getNanoseconds(),
        ];
    }

    /**
     * Returns the {@see DateTimeInterface} parts (year, month, day, hours, minutes, seconds, nanoseconds).
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
    public function getParts(): array
    {
        return [
            $this->getYear(),
            $this->getMonth(),
            $this->getDay(),
            $this->getHours(),
            $this->getMinutes(),
            $this->getSeconds(),
            $this->getNanoseconds(),
        ];
    }

    /**
     * Retrieves the era of the date represented by this DateTime instance.
     *
     * This method returns an instance of the `Era` enum, which indicates whether the date
     * falls in the Anno Domini (AD) or Before Christ (BC) era. The era is determined based on the year
     * of the date this object represents, with years designated as BC being negative
     * and years in AD being positive.
     */
    public function getEra(): Era
    {
        return Era::fromYear($this->getYear());
    }

    /**
     * Returns the century number for the year stored in this object.
     */
    public function getCentury(): int
    {
        return (int) ($this->getYear() / 100) + 1;
    }

    /**
     * Returns the short format of the year (last 2 digits).
     *
     * @return int<-99, 99> The short format of the year.
     */
    public function getYearShort(): int
    {
        return (int) $this->format(
            pattern: 'yy',
            locale: Locale::ENGLISH_UNITED_KINGDOM,
        );
    }

    /**
     * Returns the month as an instance of the {@see Month} enum.
     *
     * This method converts the numeric representation of the month into its corresponding
     * case in the {@see Month} enum, providing a type-safe way to work with months.
     *
     * @return Month The month as an enum case.
     */
    public function getMonthEnum(): Month
    {
        return Month::from($this->getMonth());
    }

    /**
     * Returns the hours using the 12-hour format (1 to 12) along with the meridiem indicator.
     *
     * @return array{int<1, 12>, Meridiem} The hours and meridiem indicator.
     */
    public function getTwelveHours(): array
    {
        $hours = $this->getHours();
        $twelve_hours = $hours % 12;
        if (0 === $twelve_hours) {
            $twelve_hours = 12;
        }

        return [$twelve_hours, $hours < 12 ? Meridiem::ANTE_MERIDIEM : Meridiem::POST_MERIDIEM];
    }

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
    public function getISOWeekNumber(): array
    {
        /** @var int<1, 53> $week */
        $week = (int) $this->format(
            pattern: 'w',
            locale: Locale::ENGLISH_UNITED_KINGDOM,
        );

        $year = (int) $this->format(
            pattern: 'Y',
            locale: Locale::ENGLISH_UNITED_KINGDOM,
        );

        return [$year, $week];
    }

    /**
     * Gets the weekday of the date.
     *
     * @return Weekday The weekday.
     */
    public function getWeekday(): Weekday
    {
        // Settings nanoseconds to 1 to a avoid rounding
        // errors with extreme values (eg. 999_999_999)
        return Weekday::from((int) $this->withNanoseconds(1)->format(
            pattern: 'e',
            locale: Locale::ENGLISH_UNITED_KINGDOM,
        ));
    }

    /**
     * Checks if the year is a leap year.
     */
    public function isLeapYear(): bool
    {
        return namespace\is_leap_year($this->getYear());
    }

    /**
     * Adds a year to this date-time object, returning a new instance with the added year.
     *
     * @throws Exception\UnexpectedValueException If adding the year results in an arithmetic issue.
     */
    public function plusYear(): static
    {
        return $this->plusYears(1);
    }

    /**
     * Adds the specified years to this date-time object, returning a new instance with the added years.
     *
     * @throws Exception\UnexpectedValueException If adding the years results in an arithmetic issue.
     */
    public function plusYears(int $years): static
    {
        return $this->plusMonths($years * MONTHS_PER_YEAR);
    }

    /**
     * Subtracts a year from this date-time object, returning a new instance with the subtracted year.
     *
     * @throws Exception\UnexpectedValueException If subtracting the year results in an arithmetic issue.
     */
    public function minusYear(): static
    {
        return $this->minusYears(1);
    }

    /**
     * Subtracts the specified years from this date-time object, returning a new instance with the subtracted years.
     *
     * @throws Exception\UnexpectedValueException If subtracting the years results in an arithmetic issue.
     */
    public function minusYears(int $years): static
    {
        return $this->minusMonths($years * MONTHS_PER_YEAR);
    }

    /**
     * Adds a month to this date-time object, returning a new instance with the added month.
     *
     * @throws Exception\UnexpectedValueException If adding the month results in an arithmetic issue.
     */
    public function plusMonth(): static
    {
        return $this->plusMonths(1);
    }

    /**
     * Adds the specified months to this date-time object, returning a new instance with the added months.
     *
     * @throws Exception\UnexpectedValueException If adding the months results in an arithmetic issue.
     */
    public function plusMonths(int $months): static
    {
        if (0 === $months) {
            return $this;
        }

        if ($months < 1) {
            return $this->minusMonths(-$months);
        }

        $plus_years = intdiv($months, MONTHS_PER_YEAR);
        $months_left = $months - ($plus_years * MONTHS_PER_YEAR);
        $target_month = $this->getMonth() + $months_left;

        if ($target_month > MONTHS_PER_YEAR) {
            $plus_years++;
            $target_month -= MONTHS_PER_YEAR;
        }

        $target_month_enum = Month::from($target_month);

        return $this->withDate(
            $target_year = $this->getYear() + $plus_years,
            $target_month_enum->value,
            Math\minva($this->getDay(), $target_month_enum->getDaysForYear($target_year)),
        );
    }

    /**
     * Subtracts a month from this date-time object, returning a new instance with the subtracted month.
     *
     * @throws Exception\UnexpectedValueException If subtracting the month results in an arithmetic issue.
     */
    public function minusMonth(): static
    {
        return $this->minusMonths(1);
    }

    /**
     * Subtracts the specified months from this date-time object, returning a new instance with the subtracted months.
     *
     * @throws Exception\UnexpectedValueException If subtracting the months results in an arithmetic issue.
     */
    public function minusMonths(int $months): static
    {
        if (0 === $months) {
            return $this;
        }

        if ($months < 1) {
            return $this->plusMonths(-$months);
        }

        $minus_years = intdiv($months, MONTHS_PER_YEAR);
        $months_left = $months - ($minus_years * MONTHS_PER_YEAR);
        $target_month = $this->getMonth() - $months_left;

        if ($target_month <= 0) {
            $minus_years++;
            $target_month = MONTHS_PER_YEAR - abs($target_month);
        }

        $target_month_enum = Month::from($target_month);

        return $this->withDate(
            $target_year = $this->getYear() - $minus_years,
            $target_month_enum->value,
            Math\minva($this->getDay(), $target_month_enum->getDaysForYear($target_year)),
        );
    }

    /**
     * Adds a day to this date-time object, returning a new instance with the added day.
     *
     * @throws Exception\UnderflowException If adding the days results in an arithmetic underflow.
     * @throws Exception\OverflowException If adding the days results in an arithmetic overflow.
     */
    public function plusDay(): static
    {
        return $this->plusDays(1);
    }

    /**
     * Adds the specified days to this date-time object, returning a new instance with the added days.
     *
     * @throws Exception\UnderflowException If adding the days results in an arithmetic underflow.
     * @throws Exception\OverflowException If adding the days results in an arithmetic overflow.
     */
    public function plusDays(int $days): static
    {
        return $this->plus(Duration::days($days));
    }

    /**
     * Subtracts a day from this date-time object, returning a new instance with the subtracted day.
     *
     * @throws Exception\UnderflowException If subtracting the days results in an arithmetic underflow.
     * @throws Exception\OverflowException If subtracting the days results in an arithmetic overflow.
     */
    public function minusDay(): static
    {
        return $this->minusDays(1);
    }

    /**
     * Subtracts the specified days from this date-time object, returning a new instance with the subtracted days.
     *
     * @throws Exception\UnderflowException If subtracting the days results in an arithmetic underflow.
     * @throws Exception\OverflowException If subtracting the days results in an arithmetic overflow.
     */
    public function minusDays(int $days): static
    {
        return $this->minus(Duration::days($days));
    }

    /**
     * Adds the specified duration to this date-time object, returning a new instance with the added duration.
     *
     * @throws Exception\UnderflowException If adding the duration results in an arithmetic underflow.
     * @throws Exception\OverflowException If adding the duration results in an arithmetic overflow.
     */
    public function plus(Duration $duration): static
    {
        return static::fromTimestamp($this->getTimestamp()->plus($duration), $this->timezone);
    }

    /**
     * Subtracts the specified duration from this date-time object, returning a new instance with the subtracted duration.
     *
     * @throws Exception\UnderflowException If subtracting the duration results in an arithmetic underflow.
     * @throws Exception\OverflowException If subtracting the duration results in an arithmetic overflow.
     */
    public function minus(Duration $duration): static
    {
        return static::fromTimestamp($this->getTimestamp()->minus($duration), $this->timezone);
    }

    /**
     * Returns a new instance set to midnight of the same day.
     */
    public function startOfDay(): static
    {
        return $this->withTime(0, 0, 0, 0);
    }

    /**
     * Returns a new instance set to the end of the day.
     */
    public function endOfDay(): static
    {
        return $this->withTime(23, 59, 59, 999_999_999);
    }

    /**
     * Returns a new instance set to the start of the week.
     */
    public function startOfWeek(): static
    {
        return $this->minusDays($this->getWeekday()->value - 1)->startOfDay();
    }

    /**
     * Returns a new instance set to the end of the week.
     */
    public function endOfWeek(): static
    {
        return $this->plusDays(7 - $this->getWeekday()->value)->endOfDay();
    }

    /**
     * Returns a new instance set to the start of the month.
     */
    public function startOfMonth(): static
    {
        return $this->withDay(1)->startOfDay();
    }

    /**
     * Returns a new instance set to the end of the month.
     */
    public function endOfMonth(): static
    {
        return $this->withDay(Month::from($this->getMonth())->getDaysForYear($this->getYear()))->endOfDay();
    }

    /**
     * Returns a new instance set to the start of the year.
     */
    public function startOfYear(): static
    {
        return $this->withDate($this->getYear(), 1, 1)->startOfDay();
    }

    /**
     * Returns a new instance set to the end of the year.
     */
    public function endOfYear(): static
    {
        return $this->withDate($this->getYear(), 12, Month::DECEMBER->getDaysForYear($this->getYear()))->endOfDay();
    }

    /**
     * Checks if this date is today.
     */
    public function isToday(): bool
    {
        $now = DateTime::now($this->getTimezone());
        return $this->getDate() === $now->getDate();
    }

    /**
     * Checks if this date is tomorrow.
     */
    public function isTomorrow(): bool
    {
        $tomorrow = DateTime::now($this->getTimezone())->plusDay();
        return $this->getDate() === $tomorrow->getDate();
    }

    /**
     * Checks if this date is yesterday.
     */
    public function isYesterday(): bool
    {
        $yesterday = DateTime::now($this->getTimezone())->minusDay();
        return $this->getDate() === $yesterday->getDate();
    }

    /**
     * Checks if this date is in the current week.
     */
    public function isCurrentWeek(): bool
    {
        $now = DateTime::now($this->getTimezone());
        $startOfThisWeek = $this->startOfWeek();
        $endOfThisWeek = $this->endOfWeek();

        return $now->betweenTimeInclusive($startOfThisWeek, $endOfThisWeek);
    }

    /**
     * Checks if this date is in the current month.
     */
    public function isCurrentMonth(): bool
    {
        $now = DateTime::now($this->getTimezone());
        return $this->getYear() === $now->getYear() && $this->getMonth() === $now->getMonth();
    }

    /**
     * Checks if this date is in the current year.
     */
    public function isCurrentYear(): bool
    {
        $now = DateTime::now($this->getTimezone());
        return $this->getYear() === $now->getYear();
    }

    /**
     * Checks if two dates are on the same day.
     */
    public function isSameDay(DateTimeInterface $other): bool
    {
        return $this->getYear() === $other->getYear() && $this->getMonth() === $other->getMonth() && $this->getDay() === $other->getDay();
    }

    /**
     * Checks if two dates are in the same week.
     */
    public function isSameWeek(DateTimeInterface $other): bool
    {
        $thisWeek = $this->getISOWeekNumber();
        $otherWeek = $other->getISOWeekNumber();

        return $thisWeek[0] === $otherWeek[0] && $thisWeek[1] === $otherWeek[1];
    }

    /**
     * Checks if two dates are in the same month.
     */
    public function isSameMonth(DateTimeInterface $other): bool
    {
        return $this->getYear() === $other->getYear() && $this->getMonth() === $other->getMonth();
    }

    /**
     * Checks if two dates are in the same year.
     */
    public function isSameYear(DateTimeInterface $other): bool
    {
        return $this->getYear() === $other->getYear();
    }

    /**
     * Checks if two dates are in the same hour.
     */
    public function isSameHour(DateTimeInterface $other): bool
    {
        return $this->isSameDay($other) && $this->getHours() === $other->getHours();
    }

    /**
     * Checks if two dates are in the same minute.
     */
    public function isSameMinute(DateTimeInterface $other): bool
    {
        return $this->isSameHour($other) && $this->getMinutes() === $other->getMinutes();
    }

    /**
     * Checks if this date is the next day after the other.
     */
    public function isNextDay(DateTimeInterface $other): bool
    {
        return $this->isSameDay($other->plusDay());
    }

    /**
     * Checks if this date is the previous day before the other.
     */
    public function isPreviousDay(DateTimeInterface $other): bool
    {
        return $this->isSameDay($other->minusDay());
    }

    /**
     * Checks if this date is in the next week.
     */
    public function isNextWeek(): bool
    {
        $now = DateTime::now($this->getTimezone());
        $nextWeek = $now->plusDays(7);

        return $this->isSameWeek($nextWeek);
    }

    /**
     * Checks if this date is in previous week.
     */
    public function isPreviousWeek(): bool
    {
        $now = DateTime::now($this->getTimezone());
        $previousWeek = $now->minusDays(7);

        return $this->isSameWeek($previousWeek);
    }

    /**
     * Checks if this date is in next month.
     */
    public function isNextMonth(): bool
    {
        $now = DateTime::now($this->getTimezone());
        $nextMonth = $now->plusMonth();

        return $this->isSameMonth($nextMonth);
    }

    /**
     * Checks if this date is in previous month.
     */
    public function isPreviousMonth(): bool
    {
        $now = DateTime::now($this->getTimezone());
        $previousMonth = $now->minusMonth();

        return $this->isSameMonth($previousMonth);
    }

    /**
     * Checks if this date is in next year.
     */
    public function isNextYear(): bool
    {
        $now = DateTime::now($this->getTimezone());
        return $this->getYear() === ($now->getYear() + 1);
    }

    /**
     * Checks if this date is in previous year.
     */
    public function isPreviousYear(): bool
    {
        $now = DateTime::now($this->getTimezone());

        return $this->getYear() === ($now->getYear() - 1);
    }

    /**
     * Checks if this date falls on a weekend (Saturday/Sunday).
     */
    public function isWeekend(): bool
    {
        $weekday = $this->getWeekday();

        return $weekday === Weekday::SATURDAY || $weekday === Weekday::SUNDAY;
    }

    /**
     * Checks if this date falls on a weekday (Monday-Friday).
     */
    public function isWeekday(): bool
    {
        return ! $this->isWeekend();
    }

    /**
     * Checks if this is the 1st day of the month.
     */
    public function isFirstDayOfMonth(): bool
    {
        return $this->getDay() === 1;
    }

    /**
     * Checks if this is the last day of the month.
     */
    public function isLastDayOfMonth(): bool
    {
        $lastDay = Month::from($this->getMonth())
            ->getDaysForYear($this->getYear());

        return $this->getDay() === $lastDay;
    }

    /**
     * Checks if this is January 1st.
     */
    public function isFirstDayOfYear(): bool
    {
        return $this->getMonth() === 1 && $this->getDay() === 1;
    }

    /**
     * Checks if this is December 31st.
     */
    public function isLastDayOfYear(): bool
    {
        return $this->getMonth() === 12 && $this->getDay() === 31;
    }

    /**
     * Checks if time is in morning (6:00-11:59).
     */
    public function isMorning(): bool
    {
        $hour = $this->getHours();

        return $hour >= 6 && $hour < 12;
    }

    /**
     * Checks if time is in afternoon (12:00-17:59).
     */
    public function isAfternoon(): bool
    {
        $hour = $this->getHours();

        return $hour >= 12 && $hour < 18;
    }

    /**
     * Checks if time is in evening (18:00-21:59).
     */
    public function isEvening(): bool
    {
        $hour = $this->getHours();

        return $hour >= 18 && $hour < 22;
    }

    /**
     * Checks if time is at night (22:00-5:59).
     */
    public function isNight(): bool
    {
        $hour = $this->getHours();

        return $hour >= 22 || $hour < 6;
    }

    /**
     * Checks if time is exactly midnight (00:00:00).
     */
    public function isMidnight(): bool
    {
        return $this->getHours() === 0 && $this->getMinutes() === 0 && $this->getSeconds() === 0;
    }

    /**
     * Checks if time is exactly noon (12:00:00).
     */
    public function isNoon(): bool
    {
        return $this->getHours() === 12 && $this->getMinutes() === 0 && $this->getSeconds() === 0;
    }

    /**
     * Checks if this is Monday.
     */
    public function isStartOfWeek(): bool
    {
        return $this->getWeekday() === Weekday::MONDAY;
    }

    /**
     * Checks if this is Sunday.
     */
    public function isEndOfWeek(): bool
    {
        return $this->getWeekday() === Weekday::SUNDAY;
    }

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
     * @param null|Locale $locale Optional locale for formatting. If null, uses the configured locale or system default as fallback.
     *
     * @return string The formatted date and time string, according to the specified pattern, timezone, and locale.
     *
     * @see https://unicode-org.github.io/icu/userguide/format_parse/datetime/#datetime-format-syntax
     * @see Locale::default()
     */
    #[\Override]
    public function format(null|FormatPattern|string $pattern = null, ?Timezone $timezone = null, ?Locale $locale = null): string
    {
        $timestamp = $this->getTimestamp();

        return namespace\create_intl_date_formatter(
            null,
            null,
            $pattern,
            $timezone ?? $this->getTimezone(),
            $locale,
        )->format($timestamp->getSeconds() + ($timestamp->getNanoseconds() / NANOSECONDS_PER_SECOND));
    }

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
    public function toRfc3339(?SecondsStyle $secondsStyle = null, bool $useZ = false): string
    {
        return namespace\format_rfc3339($this->getTimestamp(), $secondsStyle, $useZ, $this->getTimezone());
    }

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
     * $string_representation = $temporal->toString(FormatDateStyle::LONG, FormatTimeStyle::SHORT, $timezone, $locale);
     * ```
     *
     * @param null|DateStyle $dateStyle Optional style for the date portion of the output. If null, a default style is used.
     * @param null|TimeStyle $timeStyle Optional style for the time portion of the output. If null, a default style is used.
     * @param null|Timezone $timezone Optional timezone for formatting. If null, uses the current timezone.
     * @param null|Locale $locale Optional locale for formatting. If null, uses the configured locale or system default as fallback.
     *
     * @return string The string representation of the date and time, formatted according to the specified styles, timezone, and locale.
     *
     * @see DateStyle::default()
     * @see TimeStyle::default()
     * @see Locale::default()
     */
    #[\Override]
    public function toString(
        ?DateStyle $dateStyle = null,
        ?TimeStyle $timeStyle = null,
        ?Timezone $timezone = null,
        ?Locale $locale = null,
    ): string {
        $timestamp = $this->getTimestamp();

        return namespace\create_intl_date_formatter(
            dateStyle: $dateStyle,
            timeStyle: $timeStyle,
            pattern: null,
            timezone: $timezone ?? $this->getTimezone(),
            locale: $locale,
        )->format($timestamp->getSeconds());
    }
}
