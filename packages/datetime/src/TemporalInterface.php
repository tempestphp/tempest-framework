<?php

declare(strict_types=1);

namespace Tempest\DateTime;

use DateTimeInterface as NativeDateTimeInterface;
use JsonSerializable;
use Stringable;
use Tempest\Intl\Locale;
use Tempest\Support\Comparison\Comparable;
use Tempest\Support\Comparison\Equable;
use Tempest\Support\Comparison\Order;

/**
 * Represents a temporal object that can be manipulated and compared.
 *
 * @template-extends Comparable<TemporalInterface>
 * @template-extends Equable<TemporalInterface|string>
 */
interface TemporalInterface extends Comparable, Equable, JsonSerializable, Stringable
{
    /**
     * Returns a native {@see DateTimeInterface} instance for this {@see TemporalInterface} object.
     */
    public function toNativeDateTime(): NativeDateTimeInterface;

    /**
     * Returns the timestamp representation of this temporal object.
     */
    public function getTimestamp(): Timestamp;

    /**
     * Compare this {@see TemporalInterface} object to the given one.
     *
     * @param TemporalInterface $other
     */
    #[\Override]
    public function compare(mixed $other): Order;

    /**
     * Checks if this {@see TemporalInterface} object represents the same time as the given one.
     *
     * Note: this method is an alias for {@see TemporalInterface::atTheSameTime()}.
     *
     * @param TemporalInterface|string $other
     */
    #[\Override]
    public function equals(mixed $other): bool;

    /**
     * Checks if this temporal object represents the same time as the given one.
     */
    public function atTheSameTime(TemporalInterface $other): bool;

    /**
     * Checks if this temporal object is before the given one.
     */
    public function before(TemporalInterface $other): bool;

    /**
     * Checks if this temporal object is before or at the same time as the given one.
     */
    public function beforeOrAtTheSameTime(TemporalInterface $other): bool;

    /**
     * Checks if this temporal object is after the given one.
     */
    public function after(TemporalInterface $other): bool;

    /**
     * Checks if this temporal object is after or at the same time as the given one.
     */
    public function afterOrAtTheSameTime(TemporalInterface $other): bool;

    /**
     * Checks if this temporal object is between the given times (inclusive).
     */
    public function betweenTimeInclusive(TemporalInterface $a, TemporalInterface $b): bool;

    /**
     * Checks if this temporal object is between the given times (exclusive).
     */
    public function betweenTimeExclusive(TemporalInterface $a, TemporalInterface $b): bool;

    /**
     * Checks if this instance is in the future.
     */
    public function isFuture(): bool;

    /**
     * Checks if this instance is in the past.
     */
    public function isPast(): bool;

    /**
     * Adds the specified duration to this temporal object, returning a new instance with the added duration.
     *
     * @throws Exception\UnderflowException If adding the duration results in an arithmetic underflow.
     * @throws Exception\OverflowException If adding the duration results in an arithmetic overflow.
     */
    public function plus(Duration $duration): static;

    /**
     * Subtracts the specified duration from this temporal object, returning a new instance with the subtracted duration.
     *
     * @throws Exception\UnderflowException If subtracting the duration results in an arithmetic underflow.
     * @throws Exception\OverflowException If subtracting the duration results in an arithmetic overflow.
     */
    public function minus(Duration $duration): static;

    /**
     * Adds an hour to this temporal object, returning a new instance with the added hour.
     *
     * @throws Exception\UnderflowException If adding the hours results in an arithmetic underflow.
     * @throws Exception\OverflowException If adding the hours results in an arithmetic overflow.
     */
    public function plusHour(): static;

    /**
     * Adds the specified hours to this temporal object, returning a new instance with the added hours.
     *
     * @throws Exception\UnderflowException If adding the hours results in an arithmetic underflow.
     * @throws Exception\OverflowException If adding the hours results in an arithmetic overflow.
     */
    public function plusHours(int $hours): static;

    /**
     * Adds a minute to this temporal object, returning a new instance with the added minute.
     *
     * @throws Exception\UnderflowException If adding the minutes results in an arithmetic underflow.
     * @throws Exception\OverflowException If adding the minutes results in an arithmetic overflow.
     */
    public function plusMinute(): static;

    /**
     * Adds the specified minutes to this temporal object, returning a new instance with the added minutes.
     *
     * @throws Exception\UnderflowException If adding the minutes results in an arithmetic underflow.
     * @throws Exception\OverflowException If adding the minutes results in an arithmetic overflow.
     */
    public function plusMinutes(int $minutes): static;

    /**
     * Adds a second to this temporal object, returning a new instance with the added second.
     *
     * @throws Exception\UnderflowException If adding the seconds results in an arithmetic underflow.
     * @throws Exception\OverflowException If adding the seconds results in an arithmetic overflow.
     */
    public function plusSecond(): static;

    /**
     * Adds the specified seconds to this temporal object, returning a new instance with the added seconds.
     *
     * @throws Exception\UnderflowException If adding the seconds results in an arithmetic underflow.
     * @throws Exception\OverflowException If adding the seconds results in an arithmetic overflow.
     */
    public function plusSeconds(int $seconds): static;

    /**
     * Adds a millisecond to this temporal object, returning a new instance with the added millisecond.
     *
     * @throws Exception\UnderflowException If adding the milliseconds results in an arithmetic underflow.
     * @throws Exception\OverflowException If adding the milliseconds results in an arithmetic overflow.
     */
    public function plusMillisecond(): static;

    /**
     * Adds the specified milliseconds to this temporal object, returning a new instance with the added milliseconds.
     *
     * @throws Exception\UnderflowException If adding the milliseconds results in an arithmetic underflow.
     * @throws Exception\OverflowException If adding the milliseconds results in an arithmetic overflow.
     */
    public function plusMilliseconds(int $milliseconds): static;

    /**
     * Adds a nanosecond to this temporal object, returning a new instance with the added nanosecond.
     *
     * @throws Exception\UnderflowException If adding the nanoseconds results in an arithmetic underflow.
     * @throws Exception\OverflowException If adding the nanoseconds results in an arithmetic overflow.
     */
    public function plusNanosecond(): static;

    /**
     * Adds the specified nanoseconds to this temporal object, returning a new instance with the added nanoseconds.
     *
     * @throws Exception\UnderflowException If adding the nanoseconds results in an arithmetic underflow.
     * @throws Exception\OverflowException If adding the nanoseconds results in an arithmetic overflow.
     */
    public function plusNanoseconds(int $nanoseconds): static;

    /**
     * Subtracts an hour from this temporal object, returning a new instance with the subtracted hour.
     *
     * @throws Exception\UnderflowException If subtracting the hours results in an arithmetic underflow.
     * @throws Exception\OverflowException If subtracting the hours results in an arithmetic overflow.
     */
    public function minusHour(): static;

    /**
     * Subtracts the specified hours from this temporal object, returning a new instance with the subtracted hours.
     *
     * @throws Exception\UnderflowException If subtracting the hours results in an arithmetic underflow.
     * @throws Exception\OverflowException If subtracting the hours results in an arithmetic overflow.
     */
    public function minusHours(int $hours): static;

    /**
     * Subtracts a minute from this temporal object, returning a new instance with the subtracted minute.
     *
     * @throws Exception\UnderflowException If subtracting the minutes results in an arithmetic underflow.
     * @throws Exception\OverflowException If subtracting the minutes results in an arithmetic overflow.
     */
    public function minusMinute(): static;

    /**
     * Subtracts the specified minutes from this temporal object, returning a new instance with the subtracted minutes.
     *
     * @throws Exception\UnderflowException If subtracting the minutes results in an arithmetic underflow.
     * @throws Exception\OverflowException If subtracting the minutes results in an arithmetic overflow.
     */
    public function minusMinutes(int $minutes): static;

    /**
     * Subtracts a second from this temporal object, returning a new instance with the subtracted second.
     *
     * @throws Exception\UnderflowException If subtracting the seconds results in an arithmetic underflow.
     * @throws Exception\OverflowException If subtracting the seconds results in an arithmetic overflow.
     */
    public function minusSecond(): static;

    /**
     * Subtracts the specified seconds from this temporal object, returning a new instance with the subtracted seconds.
     *
     * @throws Exception\UnderflowException If subtracting the seconds results in an arithmetic underflow.
     * @throws Exception\OverflowException If subtracting the seconds results in an arithmetic overflow.
     */
    public function minusSeconds(int $seconds): static;

    /**
     * Substracts a millisecond to this temporal object, returning a new instance with the subtracted millisecond.
     *
     * @throws Exception\UnderflowException If subtracting the milliseconds results in an arithmetic underflow.
     * @throws Exception\OverflowException If subtracting the milliseconds results in an arithmetic overflow.
     */
    public function minusMillisecond(): static;

    /**
     * Substracts the specified milliseconds to this temporal object, returning a new instance with the subtracted milliseconds.
     *
     * @throws Exception\UnderflowException If subtracting the milliseconds results in an arithmetic underflow.
     * @throws Exception\OverflowException If subtracting the milliseconds results in an arithmetic overflow.
     */
    public function minusMilliseconds(int $milliseconds): static;

    /**
     * Subtracts a nanosecond from this temporal object, returning a new instance with the subtracted nanosecond.
     *
     * @throws Exception\UnderflowException If subtracting the nanoseconds results in an arithmetic underflow.
     * @throws Exception\OverflowException If subtracting the nanoseconds results in an arithmetic overflow.
     */
    public function minusNanosecond(): static;

    /**
     * Subtracts the specified nanoseconds from this temporal object, returning a new instance with the subtracted nanoseconds.
     *
     * @throws Exception\UnderflowException If subtracting the nanoseconds results in an arithmetic underflow.
     * @throws Exception\OverflowException If subtracting the nanoseconds results in an arithmetic overflow.
     */
    public function minusNanoseconds(int $nanoseconds): static;

    /**
     * Calculates the duration between this temporal object and the given one.
     *
     * @param TemporalInterface $other The temporal object to calculate the duration to.
     *
     * @return Duration The duration between the two temporal objects.
     */
    public function since(TemporalInterface $other): Duration;

    /**
     * Calculates the duration between this temporal object and the given one.
     *
     * @param TemporalInterface $other The temporal object to calculate the duration to.
     *
     * @return Duration The duration between the two temporal objects.
     */
    public function between(TemporalInterface $other): Duration;

    /**
     * Formats this {@see Tempest\DateTime\TemporalInterface} instance based on a specific pattern, with optional customization for timezone and locale.
     *
     * This method allows for detailed customization of the output string by specifying a format pattern. If no pattern is provided,
     * a default, implementation-specific pattern will be used. Additionally, the method supports specifying a timezone and locale
     * for further customization of the formatted output. If these are not provided, system defaults will be used.
     *
     * Example usage:
     *
     * ```php
     * $formatted_string = $temporal->format('yyyy-MM-dd HH:mm:ss', $timezone, $locale);
     * ```
     *
     * @param null|FormatPattern|string $pattern Optional custom format pattern for the date and time. If null, uses a default pattern.
     * @param null|Timezone $timezone Optional timezone for formatting. If null, uses the system's default timezone.
     * @param null|Locale $locale Optional locale for formatting. If null, uses the system's default locale.
     *
     * @return string The formatted {@see Tempest\DateTime\TemporalInterface} instance string, according to the specified pattern, timezone, and locale.
     *
     * @see https://unicode-org.github.io/icu/userguide/format_parse/datetime/#datetime-format-syntax
     * @see Timezone::default()
     * @see Locale::default()
     */
    public function format(null|FormatPattern|string $pattern = null, ?Timezone $timezone = null, ?Locale $locale = null): string;

    /**
     * Formats this {@see TemporalInterface} instance to a string based on the RFC 3339 format, with additional
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
     * @return string The formatted string of the {@see TemporalInterface} instance, adhering to the RFC 3339 and compatible with ISO 8601 formats.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc3339
     */
    public function toRfc3339(?SecondsStyle $secondsStyle = null, bool $useZ = false): string;

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
     * @param null|Timezone $timezone Optional timezone for formatting. If null, uses the system's default timezone.
     * @param null|Locale $locale Optional locale for formatting. If null, uses the system's default locale.
     *
     * @return string The string representation of the date and time, formatted according to the specified styles, timezone, and locale.
     *
     * @see DateStyle::default()
     * @see TimeStyle::default()
     * @see Timezone::default()
     * @see Locale::default()
     */
    public function toString(?DateStyle $dateStyle = null, ?TimeStyle $timeStyle = null, ?Timezone $timezone = null, ?Locale $locale = null): string;

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
     * Converts the current temporal object to a new {@see DateTimeInterface} instance in a different timezone.
     *
     * @param Timezone $timezone The target timezone for the conversion.
     */
    public function convertToTimezone(Timezone $timezone): DateTimeInterface;

    /**
     * Stops the execution and dumps the current state of this temporal object.
     */
    public function dd(): void;
}
