<?php

declare(strict_types=1);

namespace Tempest\DateTime;

use DateTimeInterface as NativeDateTimeInterface;
use IntlCalendar;
use Tempest\Intl\Locale;

/**
 * Represents a date and time in a specific timezone.
 */
final readonly class DateTime implements DateTimeInterface
{
    use DateTimeConvenienceMethods;

    private int $year;

    /**
     * @var int<1, 12>
     */
    private int $month;

    /**
     * @var int<1, 31>
     */
    private int $day;

    /**
     * @var int<0, 23>
     */
    private int $hours;

    /**
     * @var int<0, 59>
     */
    private int $minutes;

    /**
     * @var int<0, 59>
     */
    private int $seconds;

    /**
     * @var int<0, 999999999>
     */
    private int $nanoseconds;

    /**
     * Constructs a new date-time instance with specified components and timezone.
     *
     * This constructor initializes a date-time object with the provided year, month, day, hour, minute,
     * second, and nanosecond components within the given timezone. It ensures that all components are within their
     * valid ranges: nanoseconds [0, 999,999,999], seconds [0, 59], minutes [0, 59], hours [0, 23], month [1, 12],
     * and day [1, 28-31] depending on the month and leap year status. The constructor validates these components and
     * assigns them to the instance if they are valid. If any component is out of its valid range, an
     * `Exception\InvalidDateTimeException` is thrown.
     *
     * @throws Exception\InvalidArgumentException If any of the date or time components are outside their valid ranges,
     *                                            indicating an invalid date-time configuration.
     */
    private function __construct(
        private Timezone $timezone,
        private Timestamp $timestamp,
        int $year,
        int $month,
        int $day,
        int $hours,
        int $minutes,
        int $seconds,
        int $nanoseconds,
    ) {
        if ($nanoseconds < 0 || $nanoseconds >= NANOSECONDS_PER_SECOND) {
            throw Exception\InvalidArgumentException::forNanoseconds($nanoseconds);
        }

        if ($seconds < 0 || $seconds >= 60) {
            throw Exception\InvalidArgumentException::forSeconds($seconds);
        }

        if ($minutes < 0 || $minutes >= 60) {
            throw Exception\InvalidArgumentException::forMinutes($minutes);
        }

        if ($hours < 0 || $hours >= 24) {
            throw Exception\InvalidArgumentException::forHours($hours);
        }

        if ($month < 1 || $month > 12) {
            throw Exception\InvalidArgumentException::forMonth($month);
        }

        if ($day < 1 || $day > 31 || $day > Month::from($month)->getDaysForYear($year)) {
            throw Exception\InvalidArgumentException::forDay($day, $month, $year);
        }

        $this->year = $year;
        $this->month = $month;
        $this->day = $day;
        $this->hours = $hours;
        $this->minutes = $minutes;
        $this->seconds = $seconds;
        $this->nanoseconds = $nanoseconds;
    }

    /**
     * Creates a new {@see DateTime} instance representing the current moment.
     *
     * This static method returns a {@see DateTime} object set to the current date and time. If a specific timezone is
     * provided, the returned {@see DateTime} will be adjusted to reflect the date and time in that timezone.
     *
     * @param null|Timezone $timezone Optional timezone. If null, uses the system's default timezone.
     */
    public static function now(?Timezone $timezone = null): DateTime
    {
        return self::fromTimestamp(Timestamp::now(), $timezone);
    }

    /**
     * Creates a DateTime instance for a specific time on the current day within the specified timezone.
     *
     * This method facilitates the creation of a {@see DateTime} object representing a precise time on today's date. It is
     * particularly useful when you need to set a specific time of day for the current date in a given timezone. The
     * method combines the current date context with a specific time, offering a convenient way to specify times such
     * as "today at 14:00" in code.
     *
     * The time components (hours, minutes, seconds, nanoseconds) must be within their valid ranges. The method
     * enforces these constraints and throws an {@see Exception\InvalidArgumentException} if any component is out of bounds.
     *
     * @param int<0, 23> $hours The hour component of the time, ranging from 0 to 23.
     * @param int<0, 59> $minutes The minute component of the time, ranging from 0 to 59.
     * @param int<0, 59> $seconds The second component of the time, defaulting to 0, and ranging from 0 to 59.
     * @param int<0, 999999999> $nanoseconds The nanosecond component of the time, defaulting to 0, and ranging from 0 to 999,999,999.
     *
     * @throws Exception\UnexpectedValueException If any of the provided time components do not align with calendar expectations.
     */
    public static function todayAt(int $hours, int $minutes, int $seconds = 0, int $nanoseconds = 0, ?Timezone $timezone = null): DateTime
    {
        return self::now($timezone)->withTime($hours, $minutes, $seconds, $nanoseconds);
    }

    /**
     * Creates a {@see Tempest\DateTime\DateTime} instance from individual date and time components.
     *
     * This method constructs a DateTime object using the specified year, month, day, hour, minute, second,
     * and nanosecond components within a given timezone. It validates each component against the Gregorian calendar
     * to ensure the date and time are possible. For example, it checks for the correct range of months (1-12),
     * days in a month (considering leap years), hours (0-23), minutes (0-59), and seconds (0-59).
     *
     * Note: In cases where the specified time occurs twice (such as during the end of daylight saving time), the earlier occurrence
     * is returned. To obtain the later occurrence, you can adjust the returned instance using `->plusHour()`.
     *
     * @param Month|int<1, 12> $month
     * @param int<1, 31> $day
     * @param int<0, 23> $hours
     * @param int<0, 59> $minutes
     * @param int<0, 59> $seconds
     * @param int<0, 999999999> $nanoseconds
     *
     * @throws Exception\UnexpectedValueException If any of the provided date or time components do not align with calendar expectations.
     */
    public static function fromParts(Timezone $timezone, int $year, Month|int $month, int $day, int $hours = 0, int $minutes = 0, int $seconds = 0, int $nanoseconds = 0): self
    {
        if ($month instanceof Month) {
            $month = $month->value;
        }

        $calendar = namespace\create_intl_calendar_from_date_time(
            $timezone,
            $year,
            $month,
            $day,
            $hours,
            $minutes,
            $seconds,
        );

        if ($seconds !== $calendar->get(IntlCalendar::FIELD_SECOND)) {
            throw Exception\UnexpectedValueException::forSeconds($seconds, $calendar->get(IntlCalendar::FIELD_SECOND));
        }

        if ($minutes !== $calendar->get(IntlCalendar::FIELD_MINUTE)) {
            throw Exception\UnexpectedValueException::forMinutes($minutes, $calendar->get(IntlCalendar::FIELD_MINUTE));
        }

        if ($hours !== $calendar->get(IntlCalendar::FIELD_HOUR_OF_DAY)) {
            throw Exception\UnexpectedValueException::forHours($hours, $calendar->get(IntlCalendar::FIELD_HOUR_OF_DAY));
        }

        if ($day !== $calendar->get(IntlCalendar::FIELD_DAY_OF_MONTH)) {
            throw Exception\UnexpectedValueException::forDay($day, $calendar->get(IntlCalendar::FIELD_DAY_OF_MONTH));
        }

        if ($month !== ($calendar->get(IntlCalendar::FIELD_MONTH) + 1)) {
            throw Exception\UnexpectedValueException::forMonth($month, $calendar->get(IntlCalendar::FIELD_MONTH) + 1);
        }

        if ($year !== $calendar->get(IntlCalendar::FIELD_YEAR)) {
            throw Exception\UnexpectedValueException::forYear($year, $calendar->get(IntlCalendar::FIELD_YEAR));
        }

        $timestamp_in_seconds = (int) ($calendar->getTime() / ((float) MILLISECONDS_PER_SECOND));
        $timestamp = Timestamp::fromParts($timestamp_in_seconds, $nanoseconds);

        return new self($timezone, $timestamp, $year, $month, $day, $hours, $minutes, $seconds, $nanoseconds);
    }

    /**
     * Creates a {@see Tempest\DateTime\DateTime} instance from a timestamp, representing the same point in time.
     * If the timestamp is an integer, it must be a seconds-based timestamp.
     *
     * @param null|Timezone $timezone Optional timezone. If null, uses the system's default timezone.
     *
     * @see Timezone::default()
     */
    #[\Override]
    public static function fromTimestamp(int|Timestamp $timestamp, ?Timezone $timezone = null): static
    {
        $timezone ??= Timezone::default();

        if (is_int($timestamp)) {
            $timestamp = Timestamp::fromParts($timestamp);
        }

        /** @var IntlCalendar $calendar */
        $calendar = IntlCalendar::createInstance(namespace\to_intl_timezone($timezone));

        $calendar->setTime($timestamp->getSeconds() * MILLISECONDS_PER_SECOND);

        $year = $calendar->get(IntlCalendar::FIELD_YEAR);
        $month = $calendar->get(IntlCalendar::FIELD_MONTH) + 1;
        $day = $calendar->get(IntlCalendar::FIELD_DAY_OF_MONTH);
        $hour = $calendar->get(IntlCalendar::FIELD_HOUR_OF_DAY);
        $minute = $calendar->get(IntlCalendar::FIELD_MINUTE);
        $second = $calendar->get(IntlCalendar::FIELD_SECOND);
        $nanoseconds = $timestamp->getNanoseconds();

        return new static($timezone, $timestamp, $year, $month, $day, $hour, $minute, $second, $nanoseconds);
    }

    /**
     * Parses the given value into an instance of {@see Tempest\DateTime\DateTime}. The value may be an integer, second-based timestamp, a native datetime string, a native {@see \DateTimeInterface}, or a {@see Tempest\DateTime\DateTime}.
     *
     * The {@see fromPattern} methods is recommended instead if you already know the format of the date string to parse.
     *
     * Example usage:
     *
     * ```php
     * $parsed = DateTime\DateTime::parse($input);
     * ```
     *
     * @param NativeDateTimeInterface|TemporalInterface|string|int $string The date and time to parse.
     * @param null|Timezone $timezone Optional timezone for parsing. If null, uses the system's default timezone.
     *
     * @throws Exception\RuntimeException If the parsing process fails.
     *
     * @return static Returns an instance of {@see Tempest\DateTime\DateTime} representing the parsed date and time.
     */
    public static function parse(NativeDateTimeInterface|TemporalInterface|string|int $string, ?Timezone $timezone = null): static
    {
        if (is_int($string)) {
            return self::fromTimestamp($string);
        }

        if (is_string($string) && trim($string) === 'now') {
            return self::now($timezone);
        }

        if ($string instanceof NativeDateTimeInterface) {
            return self::fromTimestamp(
                timestamp: Timestamp::fromParts($string->getTimestamp()),
                timezone: $timezone ?? Timezone::tryFrom($string->getTimezone()?->getName() ?? ''),
            );
        }

        if ($string instanceof TemporalInterface) {
            return self::fromTimestamp($string->getTimestamp());
        }

        return self::fromTimestamp(
            timestamp: Timestamp::fromParts(new \DateTime($string)->getTimestamp()),
            timezone: $timezone,
        );
    }

    /**
     * Parses a date and time string into an instance of {@see Tempest\DateTime\DateTime} using a specific format pattern, with optional customization for timezone and locale.
     *
     *  This method is specifically designed for cases where a custom format pattern is used to parse the input string.
     * It allows for precise control over the parsing process by specifying the exact format pattern that matches the input string.
     * Additionally, the method supports specifying a timezone and locale for parsing, enabling accurate interpretation of locale-specific formats.
     *
     * Example usage:
     *
     * ```php
     * $raw_string = '2023-03-15 12:00:00';
     * $parsed_datetime = DateTime\DateTime::parse($raw_string, 'yyyy-MM-dd HH:mm:ss', DateTime\Timezone::Utc, Locale\Locale::English);
     * ```
     *
     * @param string $string The date and time string to parse.
     * @param FormatPattern|string $pattern The custom format pattern for parsing the date and time. If null, uses a default pattern.
     * @param null|Timezone $timezone Optional timezone for parsing. If null, uses the system's default timezone.
     * @param null|Locale $locale Optional locale for parsing. If null, uses the system's default locale.
     *
     * @throws Exception\RuntimeException If the parsing process fails.
     *
     * @return static Returns an instance of {@see DateTime} representing the parsed date and time.
     *
     * @see https://unicode-org.github.io/icu/userguide/format_parse/datetime/#datetime-format-syntax
     * @see TemporalInterface::format()
     */
    public static function fromPattern(string $string, FormatPattern|string $pattern, ?Timezone $timezone = null, ?Locale $locale = null): static
    {
        $timezone ??= Timezone::default();

        return self::fromTimestamp(Timestamp::fromPattern($string, $pattern, $timezone, $locale), $timezone);
    }

    /**
     * Creates an instance of {@see Tempest\DateTime\DateTime} from a date and time string, formatted according to specified styles for date and time, with optional customization for timezone and locale.
     *
     * This method provides a more abstracted approach to parsing, allowing users to specify styles rather than a custom pattern.
     * This is particularly useful for parsing strings that follow common date and time formats.
     * Additionally, the timezone and locale parameters enable accurate parsing of strings in locale-specific formats.
     *
     * Example usage:
     *
     * ```php
     * $raw_string = "March 15, 2023, 12:00 PM";
     *
     * $datetime = DateTime\DateTime::fromString($raw_string, FormatDateStyle::Long, FormatTimeStyle::Short, DateTime\Timezone::Utc, Locale\Locale::English);
     * ```
     *
     * @param string $rawString The date and time string to parse.
     * @param null|DateStyle $dateStyle The style for the date portion of the string. If null, a default style is used.
     * @param null|TimeStyle $timeStyle The style for the time portion of the string. If null, a default style is used.
     * @param null|Timezone $timezone Optional timezone for parsing. If null, uses the system's default timezone.
     * @param null|Locale $locale Optional locale for parsing. If null, uses the system's default locale.
     *
     * @throws Exception\RuntimeException If the parsing process fails.
     *
     * @return static Returns an instance of {@see DateTime} representing the parsed date and time.
     *
     * @see DateTimeInterface::toString()
     */
    public static function fromString(string $rawString, ?DateStyle $dateStyle = null, ?TimeStyle $timeStyle = null, ?Timezone $timezone = null, ?Locale $locale = null): static
    {
        $timezone ??= Timezone::default();

        return self::fromTimestamp(
            timestamp: Timestamp::fromString($rawString, $dateStyle, $timeStyle, $timezone, $locale),
            timezone: $timezone,
        );
    }

    /**
     * Returns the timestamp representation of this date time object.
     */
    #[\Override]
    public function getTimestamp(): Timestamp
    {
        return $this->timestamp;
    }

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
    #[\Override]
    public function getYear(): int
    {
        return $this->year;
    }

    /**
     * Returns the month.
     *
     * @return int<1, 12>
     */
    #[\Override]
    public function getMonth(): int
    {
        return $this->month;
    }

    /**
     * Returns the day.
     *
     * @return int<1, 31>
     */
    #[\Override]
    public function getDay(): int
    {
        return $this->day;
    }

    /**
     * Returns the hours.
     *
     * @return int<0, 23>
     */
    #[\Override]
    public function getHours(): int
    {
        return $this->hours;
    }

    /**
     * Returns the minutes.
     *
     * @return int<0, 59>
     */
    #[\Override]
    public function getMinutes(): int
    {
        return $this->minutes;
    }

    /**
     * Returns the seconds.
     *
     * @return int<0, 59>
     */
    #[\Override]
    public function getSeconds(): int
    {
        return $this->seconds;
    }

    /**
     * Returns the nanoseconds.
     *
     * @return int<0, 999999999>
     */
    #[\Override]
    public function getNanoseconds(): int
    {
        return $this->nanoseconds;
    }

    /**
     * Gets the timezone associated with the date and time.
     */
    #[\Override]
    public function getTimezone(): Timezone
    {
        return $this->timezone;
    }

    /**
     * Returns a new instance with the specified date.
     *
     * @param Month|int<1, 12> $month
     * @param int<1, 31> $day
     *
     * @throws Exception\UnexpectedValueException If any of the provided date components do not align with calendar expectations.
     */
    #[\Override]
    public function withDate(int $year, Month|int $month, int $day): static
    {
        return static::fromParts(
            $this->timezone,
            $year,
            $month,
            $day,
            $this->hours,
            $this->minutes,
            $this->seconds,
            $this->nanoseconds,
        );
    }

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
    #[\Override]
    public function withTime(int $hours, int $minutes, int $seconds = 0, int $nanoseconds = 0): static
    {
        return static::fromParts(
            $this->timezone,
            $this->year,
            $this->month,
            $this->day,
            $hours,
            $minutes,
            $seconds,
            $nanoseconds,
        );
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
        return $this->withDay($this->day - ($this->getWeekday()->value - 1))->startOfDay();
    }

    /**
     * Returns a new instance set to the end of the week.
     */
    public function endOfWeek(): static
    {
        return $this->withDay($this->getDay() + (7 - $this->getWeekday()->value))->endOfDay();
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
        return $this->withDay(Month::from($this->month)->getDaysForYear($this->year))->endOfDay();
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

    #[\Override]
    public function jsonSerialize(): array
    {
        return [
            'timezone' => $this->timezone,
            'timestamp' => $this->timestamp,
            'year' => $this->year,
            'month' => $this->month,
            'day' => $this->day,
            'hours' => $this->hours,
            'minutes' => $this->minutes,
            'seconds' => $this->seconds,
            'nanoseconds' => $this->nanoseconds,
        ];
    }
}
