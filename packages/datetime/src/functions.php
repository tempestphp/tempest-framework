<?php

namespace Tempest\DateTime {
    use IntlCalendar;
    use IntlDateFormatter;
    use IntlTimeZone;
    use RuntimeException;
    use Tempest\DateTime\DateStyle;
    use Tempest\DateTime\Exception\ParserException;
    use Tempest\DateTime\FormatPattern;
    use Tempest\DateTime\SecondsStyle;
    use Tempest\DateTime\Timestamp;
    use Tempest\DateTime\TimeStyle;
    use Tempest\DateTime\Timezone;
    use Tempest\Intl\Locale;

    use function hrtime;
    use function microtime;

    use const Tempest\DateTime\NANOSECONDS_PER_SECOND;

    /**
     * Get the current date and time as a {@see \Tempest\DateTime\DateTime} object.
     */
    function now(): DateTime
    {
        return DateTime::now();
    }

    /**
     * Check if the given year is a leap year.
     *
     * Returns true if the specified year is a leap year according to the Gregorian
     * calendar; otherwise, returns false.
     *
     * @return bool True if the year is a leap year, false otherwise.
     */
    function is_leap_year(int $year): bool
    {
        return ($year % 4) === 0 && (($year % 100) !== 0 || ($year % 400) === 0);
    }

    /**
     * @internal
     *
     * @mago-expect best-practices/no-else-clause
     */
    function format_rfc3339(Timestamp $timestamp, ?SecondsStyle $secondsStyle = null, bool $useZ = false, ?Timezone $timezone = null): string
    {
        $secondsStyle ??= SecondsStyle::fromTimestamp($timestamp);

        if (null === $timezone) {
            $timezone = Timezone::UTC;
        } elseif ($useZ) {
            $useZ = Timezone::UTC === $timezone;
        }

        $seconds = $timestamp->getSeconds();
        $nanoseconds = $timestamp->getNanoseconds();

        // Intl formatter cannot handle nanoseconds and microseconds, do it manually instead.
        $fraction = substr((string) $nanoseconds, 0, $secondsStyle->value);

        if ($fraction !== '') {
            $fraction = '.' . $fraction;
        }

        $pattern = match ($useZ) {
            true => "yyyy-MM-dd'T'HH:mm:ss@ZZZZZ",
            false => "yyyy-MM-dd'T'HH:mm:ss@xxx",
        };

        $formatter = namespace\create_intl_date_formatter(
            pattern: $pattern,
            timezone: $timezone,
        );

        $rfcString = $formatter->format($seconds);

        return str_replace('@', $fraction, $rfcString);
    }

    /**
     * @internal
     */
    function create_intl_date_formatter(
        ?DateStyle $dateStyle = null,
        ?TimeStyle $timeStyle = null,
        null|FormatPattern|string $pattern = null,
        ?Timezone $timezone = null,
        ?Locale $locale = null,
    ): IntlDateFormatter {
        if ($pattern instanceof FormatPattern) {
            $pattern = $pattern->value;
        }

        $dateStyle ??= DateStyle::default();
        $timeStyle ??= TimeStyle::default();
        $locale ??= Locale::default();
        $timezone ??= Timezone::default();

        return new IntlDateFormatter(
            locale: $locale->value,
            dateType: match ($dateStyle) {
                DateStyle::NONE => IntlDateFormatter::NONE,
                DateStyle::SHORT => IntlDateFormatter::SHORT,
                DateStyle::MEDIUM => IntlDateFormatter::MEDIUM,
                DateStyle::LONG => IntlDateFormatter::LONG,
                DateStyle::FULL => IntlDateFormatter::FULL,
                DateStyle::RELATIVE_SHORT => IntlDateFormatter::RELATIVE_SHORT,
                DateStyle::RELATIVE_MEDIUM => IntlDateFormatter::RELATIVE_MEDIUM,
                DateStyle::RELATIVE_LONG => IntlDateFormatter::RELATIVE_LONG,
                DateStyle::RELATIVE_FULL => IntlDateFormatter::RELATIVE_FULL,
            },
            timeType: match ($timeStyle) {
                TimeStyle::NONE => IntlDateFormatter::NONE,
                TimeStyle::SHORT => IntlDateFormatter::SHORT,
                TimeStyle::MEDIUM => IntlDateFormatter::MEDIUM,
                TimeStyle::LONG => IntlDateFormatter::LONG,
                TimeStyle::FULL => IntlDateFormatter::FULL,
            },
            timezone: namespace\to_intl_timezone($timezone),
            calendar: IntlDateFormatter::GREGORIAN,
            pattern: $pattern,
        );
    }

    /**
     * @internal
     */
    function default_timezone(): Timezone
    {
        /**
         * `date_default_timezone_get` function might return any of the "Others" timezones
         * mentioned in PHP doc: https://www.php.net/manual/en/timezones.others.php.
         *
         * Those timezones are not supported by Tempest (aside from UTC), as they are considered "legacy".
         */
        $timezoneId = date_default_timezone_get();

        return Timezone::tryFrom($timezoneId) ?? Timezone::UTC;
    }

    /**
     * @return array{int, int}
     *
     * @internal
     *
     * @mago-expect best-practices/no-boolean-literal-comparison
     * @mago-expect best-practices/no-else-clause
     */
    function high_resolution_time(): array
    {
        /**
         * @var null|array{int, int} $offset
         */
        static $offset = null;

        if ($offset === null) {
            $offset = hrtime();

            if ($offset === false) { // @phpstan-ignore-line identical.alwaysFalse
                throw new \RuntimeException('The system does not provide a monotonic timer.');
            }

            $time = system_time();

            $offset = [
                $time[0] - $offset[0],
                $time[1] - $offset[1],
            ];
        }

        [$secondsOffset, $nanosecondsOffset] = $offset;
        [$seconds, $nanoseconds] = hrtime();

        $nanosecondsAdjusted = $nanoseconds + $nanosecondsOffset;

        if ($nanosecondsAdjusted >= NANOSECONDS_PER_SECOND) {
            ++$seconds;
            $nanosecondsAdjusted -= NANOSECONDS_PER_SECOND;
        } elseif ($nanosecondsAdjusted < 0) {
            --$seconds;
            $nanosecondsAdjusted += NANOSECONDS_PER_SECOND;
        }

        $seconds += $secondsOffset;
        $nanoseconds = $nanosecondsAdjusted;

        return [$seconds, $nanoseconds];
    }

    /**
     * @internal
     *
     * @mago-expect best-practices/no-boolean-literal-comparison
     */
    function intl_parse(
        string $rawString,
        ?DateStyle $dateStyle = null,
        ?TimeStyle $timeStyle = null,
        null|FormatPattern|string $pattern = null,
        ?Timezone $timezone = null,
        ?Locale $locale = null,
    ): int {
        $formatter = namespace\create_intl_date_formatter($dateStyle, $timeStyle, $pattern, $timezone, $locale);

        $timestamp = $formatter->parse($rawString);

        if ($timestamp === false) {
            // Only show pattern in the exception if it was provided.
            if (null !== $pattern) {
                $formatter_pattern = ($pattern instanceof FormatPattern) ? $pattern->value : $pattern;

                throw new ParserException(sprintf(
                    "Unable to interpret '%s' as a valid date/time using pattern '%s'.",
                    $rawString,
                    $formatter_pattern,
                ));
            }

            throw new ParserException("Unable to interpret '{$rawString}' as a valid date/time.");
        }

        return (int) $timestamp;
    }

    /**
     * @return array{int, int}
     *
     * @internal
     */
    function system_time(): array
    {
        $time = microtime();

        $parts = explode(' ', $time);
        $seconds = (int) $parts[1];
        $nanoseconds = (int) (((float) $parts[0]) * ((float) NANOSECONDS_PER_SECOND));

        return [$seconds, $nanoseconds];
    }

    /**
     * @internal
     */
    function to_intl_timezone(Timezone $timezone): IntlTimeZone
    {
        $value = $timezone->value;

        if (str_starts_with($value, '+') || str_starts_with($value, '-')) {
            $value = 'GMT' . $value;
        }

        $tz = IntlTimeZone::createTimeZone($value);

        if ($tz === null) { // @phpstan-ignore-line identical.alwaysFalse
            throw new \RuntimeException(sprintf(
                'Failed to create intl timezone from timezone "%s" ("%s" / "%s").',
                $timezone->name,
                $timezone->value,
                $value,
            ));
        }

        if ($tz->getID() === 'Etc/Unknown' && $tz->getRawOffset() === 0) {
            throw new \RuntimeException(sprintf(
                'Failed to create a valid intl timezone, unknown timezone "%s" ("%s" / "%s") given.',
                $timezone->name,
                $timezone->value,
                $value,
            ));
        }

        return $tz;
    }

    /**
     * @internal
     *
     * @mago-expect best-practices/no-else-clause
     */
    function create_intl_calendar_from_date_time(
        Timezone $timezone,
        int $year,
        int $month,
        int $day,
        int $hours,
        int $minutes,
        int $seconds,
    ): IntlCalendar {
        /**
         * @var IntlCalendar $calendar
         */
        $calendar = IntlCalendar::createInstance(to_intl_timezone($timezone));

        $calendar->setDateTime($year, $month - 1, $day, $hours, $minutes, $seconds);

        return $calendar;
    }
}
