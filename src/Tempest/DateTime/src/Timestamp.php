<?php

declare(strict_types=1);

namespace Tempest\DateTime;

use Tempest\Support\Language\Locale;
use Tempest\Support\Math;

/**
 * Represents a precise point in time, with seconds and nanoseconds since the Unix epoch.
 *
 * @immutable
 */
final readonly class Timestamp implements TemporalInterface
{
    use TemporalConvenienceMethods;

    /**
     * @param int<0, 999999999> $nanoseconds
     */
    private function __construct(
        private int $seconds,
        private int $nanoseconds,
    ) {}

    /**
     * Creates a timestamp from seconds and nanoseconds since the epoch.
     *
     * Normalizes so nanoseconds are within 0-999999999. For instance:
     * - `fromRaw(42, -100)` becomes (41, 999999900).
     * - `fromRaw(-42, -100)` becomes (-43, 999999900).
     * - `fromRaw(42, 1000000100)` becomes (43, 100).
     *
     * @param int $seconds Seconds since the epoch.
     * @param int $nanoseconds Additional nanoseconds to adjust by.
     *
     * @throws Exception\OverflowException
     * @throws Exception\UnderflowException
     */
    public static function fromParts(int $seconds, int $nanoseconds = 0): Timestamp
    {
        // Check for potential overflow or underflow before doing any operation
        if ($seconds === Math\INT64_MAX && $nanoseconds >= NANOSECONDS_PER_SECOND) {
            throw new Exception\OverflowException('Adding nanoseconds would cause an overflow.');
        }

        if ($seconds === Math\INT64_MIN && $nanoseconds <= -NANOSECONDS_PER_SECOND) {
            throw new Exception\UnderflowException('Subtracting nanoseconds would cause an underflow.');
        }

        $seconds_adjustment = Math\div($nanoseconds, NANOSECONDS_PER_SECOND);
        $adjusted_seconds = $seconds + $seconds_adjustment;

        $adjusted_nanoseconds = $nanoseconds % NANOSECONDS_PER_SECOND;
        if ($adjusted_nanoseconds < 0) {
            --$adjusted_seconds;
            $adjusted_nanoseconds += NANOSECONDS_PER_SECOND;
        }

        return new self($adjusted_seconds, $adjusted_nanoseconds);
    }

    /**
     * Create a high-precision instance representing the current time using the system clock.
     */
    public static function now(): self
    {
        [$seconds, $nanoseconds] = namespace\system_time();

        return self::fromParts($seconds, $nanoseconds);
    }

    /**
     * Create a current time instance using a monotonic clock with high precision to the nanosecond for precise measurements.
     *
     * This method ensures that the time is always moving forward, unaffected by adjustments in the system clock,
     * making it suitable for measuring durations or intervals accurately.
     *
     * @throws \RuntimeException If the system does not provide a monotonic timer.
     */
    public static function monotonic(): self
    {
        [$seconds, $nanoseconds] = namespace\high_resolution_time();

        return self::fromParts($seconds, $nanoseconds);
    }

    /**
     * Parses a date and time string into an instance of {@see Timestamp} using a specific format pattern, with optional customization for timezone and locale.
     *
     * This method is specifically designed for cases where a custom format pattern is used to parse the input string.
     *
     * It allows for precise control over the parsing process by specifying the exact format pattern that matches the input string.
     *
     * Additionally, the method supports specifying a timezone and locale for parsing, enabling accurate interpretation of locale-specific formats.
     *
     * Example usage:
     *
     * ```php
     * $raw_string = '2023-03-15 12:00:00';
     * $parsed_timestamp = DateTime\Timestamp::parse($raw_string, 'yyyy-MM-dd HH:mm:ss', DateTime\Timezone::UTC, Locale\Locale::ENGLISH);
     * ```
     *
     * @param string $rawString The date and time string to parse.
     * @param FormatPattern|string $pattern The custom format pattern for parsing the date and time. If null, uses a default pattern.
     * @param null|Timezone $timezone Optional timezone for parsing. If null, uses the system's default timezone.
     * @param null|Locale $locale Optional locale for parsing. If null, uses the system's default locale.
     *
     * @throws Exception\RuntimeException If the parsing process fails.
     *
     * @return static Returns an instance of {@see Timestamp} representing the parsed date and time.
     *
     * @see https://unicode-org.github.io/icu/userguide/format_parse/datetime/#datetime-format-syntax
     * @see TemporalInterface::format()
     */
    public static function fromPattern(string $rawString, FormatPattern|string $pattern, ?Timezone $timezone = null, ?Locale $locale = null): static
    {
        return self::fromParts(intl_parse(
            rawString: $rawString,
            pattern: $pattern,
            timezone: $timezone,
            locale: $locale,
        ));
    }

    /**
     * Creates an instance of {@see Timestamp} from a date and time string, formatted according to specified styles for date and time,
     * with optional customization for timezone and locale.
     *
     * This method provides a more abstracted approach to parsing, allowing users to specify styles rather than a custom pattern.
     *
     * This is particularly useful for parsing strings that follow common date and time formats.
     *
     * Additionally, the timezone and locale parameters enable accurate parsing of strings in locale-specific formats.
     *
     * Example usage:
     *
     * ```php
     * $raw_string = "March 15, 2023, 12:00 PM";
     *
     * $timestamp = DateTime\Timestamp::fromString($raw_string, FormatDateStyle::LONG, FormatTimeStyle::SHORT, DateTime\Timezone::UTC, Locale\Locale::ENGLISH);
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
     * @return static Returns an instance of {@see Timestamp} representing the parsed date and time.
     *
     * @see TemporalInterface::toString()
     */
    public static function fromString(string $rawString, ?DateStyle $dateStyle = null, ?TimeStyle $timeStyle = null, ?Timezone $timezone = null, ?Locale $locale = null): static
    {
        return self::fromParts(intl_parse(
            rawString: $rawString,
            dateStyle: $dateStyle,
            timeStyle: $timeStyle,
            timezone: $timezone,
            locale: $locale,
        ));
    }

    /**
     * Returns this Timestamp instance itself, as it already represents a timestamp.
     */
    #[\Override]
    public function getTimestamp(): self
    {
        return $this;
    }

    /**
     * Returns the {@see Timestamp} parts (seconds, nanoseconds).
     *
     * @return array{int, int<0, 999999999>}
     */
    public function toParts(): array
    {
        return [$this->seconds, $this->nanoseconds];
    }

    /**
     * Returns the number of seconds since the Unix epoch represented by this timestamp.
     *
     * @return int Seconds since the epoch. Can be negative for times before the epoch.
     */
    public function getSeconds(): int
    {
        return $this->seconds;
    }

    /**
     * Returns the nanoseconds part of this timestamp.
     *
     * @return int<0, 999999999> The nanoseconds part, ranging from 0 to 999999999.
     */
    public function getNanoseconds(): int
    {
        return $this->nanoseconds;
    }

    /**
     * Adds the specified duration to this timestamp object, returning a new instance with the added duration.
     *
     * @throws Exception\UnderflowException If adding the duration results in an arithmetic underflow.
     * @throws Exception\OverflowException If adding the duration results in an arithmetic overflow.
     */
    #[\Override]
    public function plus(Duration $duration): static
    {
        [$h, $m, $s, $ns] = $duration->getParts();
        $totalSeconds = (SECONDS_PER_MINUTE * $m) + (SECONDS_PER_HOUR * $h) + $s;
        $newSeconds = $this->seconds + $totalSeconds;
        $newNanoseconds = $this->nanoseconds + $ns;

        // No manual normalization required here due to fromRaw handling it
        return self::fromParts($newSeconds, $newNanoseconds);
    }

    /**
     * Subtracts the specified duration from this timestamp object, returning a new instance with the subtracted duration.
     *
     * @throws Exception\UnderflowException If subtracting the duration results in an arithmetic underflow.
     * @throws Exception\OverflowException If subtracting the duration results in an arithmetic overflow.
     */
    #[\Override]
    public function minus(Duration $duration): static
    {
        [$h, $m, $s, $ns] = $duration->getParts();
        $totalSeconds = (SECONDS_PER_MINUTE * $m) + (SECONDS_PER_HOUR * $h) + $s;
        $newSeconds = $this->seconds - $totalSeconds;
        $newNanoseconds = $this->nanoseconds - $ns;

        // No manual normalization required here due to fromRaw handling it
        return self::fromParts($newSeconds, $newNanoseconds);
    }

    #[\Override]
    public function jsonSerialize(): array
    {
        return [
            'seconds' => $this->seconds,
            'nanoseconds' => $this->nanoseconds,
        ];
    }
}
