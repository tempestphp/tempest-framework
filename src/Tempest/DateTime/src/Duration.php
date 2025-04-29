<?php

declare(strict_types=1);

namespace Tempest\DateTime;

use JsonSerializable;
use Stringable;
use Tempest\Support\Comparison;
use Tempest\Support\Str;

/**
 * Defines a representation of a time duration with specific hours, minutes, seconds,
 * and nanoseconds.
 *
 * All instances are normalized as follows:
 *
 * - all non-zero parts (hours, minutes, seconds, nanoseconds) will have the same sign
 * - minutes, seconds will be between -59 and 59
 * - nanoseconds will be between -999999999 and 999999999 (less than 1 second)
 *
 * For example, Duration::hours(2, -183) normalizes to "-1 hour(s), -3 minute(s)".
 *
 * @implements Comparison\Comparable<Duration>
 * @implements Comparison\Equable<Duration>
 */
final readonly class Duration implements Comparison\Comparable, Comparison\Equable, JsonSerializable, Stringable
{
    /**
     * Initializes a new instance of Duration with specified hours, minutes, seconds, and
     * nanoseconds.
     *
     * @param int<-59, 59> $minutes
     * @param int<-59, 59> $seconds
     * @param int<-999999999, 999999999> $nanoseconds
     */
    private function __construct(
        private int $hours,
        private int $minutes,
        private int $seconds,
        private int $nanoseconds,
    ) {}

    /**
     * Returns an instance representing the specified number of hours (and
     * optionally minutes, seconds, nanoseconds). Due to normalization, the
     * actual values in the returned instance may differ from the provided ones.
     *
     *
     * @mago-expect best-practices/no-else-clause
     */
    public static function fromParts(int $hours, int $minutes = 0, int $seconds = 0, int $nanoseconds = 0): self
    {
        // This is where the normalization happens.
        $s = (SECONDS_PER_HOUR * $hours) + (SECONDS_PER_MINUTE * $minutes) + $seconds + ((int) ($nanoseconds / NANOSECONDS_PER_SECOND));
        $ns = $nanoseconds % NANOSECONDS_PER_SECOND;
        if ($s < 0 && $ns > 0) {
            ++$s;
            $ns -= NANOSECONDS_PER_SECOND;
        } elseif ($s > 0 && $ns < 0) {
            --$s;
            $ns += NANOSECONDS_PER_SECOND;
        }

        $m = (int) ($s / 60);
        $s %= 60;
        $h = (int) ($m / 60);
        $m %= 60;
        return new self($h, $m, $s, $ns);
    }

    /**
     * Returns an instance representing the specified number of weeks, in hours.
     *
     * For example, `Duration::weeks(1)` is equivalent to `Duration::hours(168)`.
     *
     */
    public static function weeks(int $weeks): self
    {
        return self::fromParts($weeks * HOURS_PER_WEEK);
    }

    /**
     * Returns an instance representing the specified number of days, in hours.
     *
     * For example, `Duration::days(2)` is equivalent to `Duration::hours(48)`.
     *
     */
    public static function days(int $days): self
    {
        return self::fromParts($days * HOURS_PER_DAY);
    }

    /**
     * Returns an instance representing the specified number of hours.
     *
     */
    public static function hours(int $hours): self
    {
        return self::fromParts($hours);
    }

    /**
     * Returns an instance representing the specified number of minutes. Due to
     * normalization, the actual value in the returned instance may differ from
     * the provided one, and the resulting instance may contain larger units.
     *
     * For example, `Duration::minutes(63)` normalizes to "1 hour(s), 3 minute(s)".
     *
     */
    public static function minutes(int $minutes): self
    {
        return self::fromParts(0, $minutes);
    }

    /**
     * Returns an instance representing the specified number of seconds. Due to
     * normalization, the actual value in the returned instance may differ from
     * the provided one, and the resulting instance may contain larger units.
     *
     * For example, `Duration::seconds(63)` normalizes to "1 minute(s), 3 second(s)".
     *
     */
    public static function seconds(int $seconds): self
    {
        return self::fromParts(0, 0, $seconds);
    }

    /**
     * Returns an instance representing the specified number of milliseconds (ms).
     * The value is converted and stored as nanoseconds, since that is the only
     * unit smaller than a second that we support. Due to normalization, the
     * resulting instance may contain larger units.
     *
     * For example, `Duration::milliseconds(8042)` normalizes to "8 second(s), 42000000 nanosecond(s)".
     *
     */
    public static function milliseconds(int $milliseconds): self
    {
        return self::fromParts(0, 0, 0, NANOSECONDS_PER_MILLISECOND * $milliseconds);
    }

    /**
     * Returns an instance representing the specified number of microseconds (us).
     * The value is converted and stored as nanoseconds, since that is the only
     * unit smaller than a second that we support. Due to normalization, the
     * resulting instance may contain larger units.
     *
     * For example, `Duration::microseconds(8000042)` normalizes to "8 second(s), 42000 nanosecond(s)".
     *
     */
    public static function microseconds(int $microseconds): self
    {
        return self::fromParts(0, 0, 0, NANOSECONDS_PER_MICROSECOND * $microseconds);
    }

    /**
     * Returns an instance representing the specified number of nanoseconds (ns).
     * Due to normalization, the resulting instance may contain larger units.
     *
     * For example, `Duration::nanoseconds(8000000042)` normalizes to "8 second(s), 42 nanosecond(s)".
     *
     */
    public static function nanoseconds(int $nanoseconds): self
    {
        return self::fromParts(0, 0, 0, $nanoseconds);
    }

    /**
     * Returns an instance with all parts equal to 0.
     *
     */
    public static function zero(): self
    {
        return new self(0, 0, 0, 0);
    }

    /**
     * Compiles and returns the duration's components (hours, minutes, seconds, nanoseconds) in an
     * array, in descending order of significance.
     *
     * @return array{int, int, int, int}
     */
    public function getParts(): array
    {
        return [$this->hours, $this->minutes, $this->seconds, $this->nanoseconds];
    }

    /**
     * Returns the "hours" part of this time duration.
     */
    public function getHours(): int
    {
        return $this->hours;
    }

    /**
     * Returns the "minutes" part of this time duration.
     */
    public function getMinutes(): int
    {
        return $this->minutes;
    }

    /**
     * Returns the "seconds" part of this time duration.
     */
    public function getSeconds(): int
    {
        return $this->seconds;
    }

    /**
     * Returns the "nanoseconds" part of this time duration.
     */
    public function getNanoseconds(): int
    {
        return $this->nanoseconds;
    }

    /**
     * Computes, and returns the total duration of the instance in hours as a floating-point number,
     * including any fractional parts.
     */
    public function getTotalHours(): float
    {
        return $this->hours + ($this->minutes / MINUTES_PER_HOUR) + ($this->seconds / SECONDS_PER_HOUR) + ($this->nanoseconds / (SECONDS_PER_HOUR * NANOSECONDS_PER_SECOND));
    }

    /**
     * Computes, and returns the total duration of the instance in minutes as a floating-point number,
     * including any fractional parts.
     */
    public function getTotalMinutes(): float
    {
        return ($this->hours * MINUTES_PER_HOUR) + $this->minutes + ($this->seconds / SECONDS_PER_MINUTE) + ($this->nanoseconds / (SECONDS_PER_MINUTE * NANOSECONDS_PER_SECOND));
    }

    /**
     * Computes, and returns the total duration of the instance in seconds as a floating-point number,
     * including any fractional parts.
     */
    public function getTotalSeconds(): float
    {
        return $this->seconds + ($this->minutes * SECONDS_PER_MINUTE) + ($this->hours * SECONDS_PER_HOUR) + ($this->nanoseconds / NANOSECONDS_PER_SECOND);
    }

    /**
     * Computes, and returns the total duration of the instance in milliseconds as a floating-point number,
     * including any fractional parts.
     */
    public function getTotalMilliseconds(): float
    {
        return (
            ($this->hours * SECONDS_PER_HOUR * MILLISECONDS_PER_SECOND) +
            ($this->minutes * SECONDS_PER_MINUTE * MILLISECONDS_PER_SECOND) +
            ($this->seconds * MILLISECONDS_PER_SECOND) +
            ($this->nanoseconds / NANOSECONDS_PER_MILLISECOND)
        );
    }

    /**
     * Computes, and returns the total duration of the instance in microseconds as a floating-point number,
     * including any fractional parts.
     */
    public function getTotalMicroseconds(): float
    {
        return (
            ($this->hours * SECONDS_PER_HOUR * MICROSECONDS_PER_SECOND) +
            ($this->minutes * SECONDS_PER_MINUTE * MICROSECONDS_PER_SECOND) +
            ($this->seconds * MICROSECONDS_PER_SECOND) +
            ($this->nanoseconds / NANOSECONDS_PER_MICROSECOND)
        );
    }

    /**
     * Determines whether the instance represents a zero duration.
     */
    public function isZero(): bool
    {
        return $this->hours === 0 && $this->minutes === 0 && $this->seconds === 0 && $this->nanoseconds === 0;
    }

    /**
     * Checks if the duration is positive, implying that all non-zero components are positive.
     *
     * Due to normalization, it is guaranteed that a positive time duration will
     * have all of its parts (hours, minutes, seconds, nanoseconds) positive or
     * equal to 0.
     *
     * Note that this method returns false if all parts are equal to 0.
     */
    public function isPositive(): bool
    {
        return $this->hours > 0 || $this->minutes > 0 || $this->seconds > 0 || $this->nanoseconds > 0;
    }

    /**
     * Checks if the duration is negative, implying that all non-zero components are negative.
     *
     * Due to normalization, it is guaranteed that a negative time duration will
     * have all of its parts (hours, minutes, seconds, nanoseconds) negative or
     * equal to 0.
     *
     * Note that this method returns false if all parts are equal to 0.
     */
    public function isNegative(): bool
    {
        return $this->hours < 0 || $this->minutes < 0 || $this->seconds < 0 || $this->nanoseconds < 0;
    }

    /**
     * Returns a new instance with the "hours" part changed to the specified
     * value.
     *
     * Note that due to normalization, the actual value in the returned
     * instance may differ, and this may affect other parts of the returned
     * instance too.
     *
     * For example, `Duration::hours(2, 30)->withHours(-1)` is equivalent to
     * `Duration::hours(-1, 30)` which normalizes to "-30 minute(s)".
     */
    public function withHours(int $hours): self
    {
        return self::fromParts($hours, $this->minutes, $this->seconds, $this->nanoseconds);
    }

    /**
     * Returns a new instance with the "minutes" part changed to the specified
     * value.
     *
     * Note that due to normalization, the actual value in the returned
     * instance may differ, and this may affect other parts of the returned
     * instance too.
     *
     * For example, `Duration::minutes(2, 30)->withMinutes(-1)` is equivalent to
     * `Duration::minutes(-1, 30)` which normalizes to "-30 second(s)".
     */
    public function withMinutes(int $minutes): self
    {
        return self::fromParts($this->hours, $minutes, $this->seconds, $this->nanoseconds);
    }

    /**
     * Returns a new instance with the "seconds" part changed to the specified
     * value.
     *
     * Note that due to normalization, the actual value in the returned
     * instance may differ, and this may affect other parts of the returned
     * instance too.
     *
     * For example, `Duration::minutes(2, 30)->withSeconds(-30)` is equivalent
     * to `Duration::minutes(2, -30)` which normalizes to "1 minute(s), 30 second(s)".
     */
    public function withSeconds(int $seconds): self
    {
        return self::fromParts($this->hours, $this->minutes, $seconds, $this->nanoseconds);
    }

    /**
     * Returns a new instance with the "nanoseconds" part changed to the specified
     * value.
     *
     * Note that due to normalization, the actual value in the returned
     * instance may differ, and this may affect other parts of the returned
     * instance too.
     *
     * For example, `Duration::seconds(2)->withNanoseconds(-1)` is equivalent
     * to `Duration::seconds(2, -1)` which normalizes to "1 second(s), 999999999 nanosecond(s)".
     */
    public function withNanoseconds(int $nanoseconds): self
    {
        return self::fromParts($this->hours, $this->minutes, $this->seconds, $nanoseconds);
    }

    /**
     * Implements a comparison between this duration and another, based on their duration.
     *
     * @param Duration $other
     */
    #[\Override]
    public function compare(mixed $other): Comparison\Order
    {
        if ($this->hours !== $other->hours) {
            return Comparison\Order::from($this->hours <=> $other->hours);
        }

        if ($this->minutes !== $other->minutes) {
            return Comparison\Order::from($this->minutes <=> $other->minutes);
        }

        if ($this->seconds !== $other->seconds) {
            return Comparison\Order::from($this->seconds <=> $other->seconds);
        }

        return Comparison\Order::from($this->nanoseconds <=> $other->nanoseconds);
    }

    /**
     * Evaluates whether this duration is equivalent to another, considering all time components.
     *
     * @param Duration $other
     */
    #[\Override]
    public function equals(mixed $other): bool
    {
        return $this->compare($other) === Comparison\Order::EQUAL;
    }

    /**
     * Determines if this duration is shorter than another.
     */
    public function shorter(self $other): bool
    {
        return $this->compare($other) === Comparison\Order::LESS;
    }

    /**
     * Determines if this duration is shorter than, or equivalent to another.
     */
    public function shorterOrEqual(self $other): bool
    {
        return $this->compare($other) !== Comparison\Order::GREATER;
    }

    /**
     * Determines if this duration is longer than another.
     */
    public function longer(self $other): bool
    {
        return $this->compare($other) === Comparison\Order::GREATER;
    }

    /**
     * Determines if this duration is longer than, or equivalent to another.
     */
    public function longerOrEqual(self $other): bool
    {
        return $this->compare($other) !== Comparison\Order::LESS;
    }

    /**
     * Returns true if this instance represents a time duration longer than $a but
     * shorter than $b, or vice-versa (shorter than $a but longer than $b), or if
     * this instance is equal to $a and/or $b. Returns false if this instance is
     * shorter/longer than both.
     */
    public function betweenInclusive(self $a, self $b): bool
    {
        $ca = $this->compare($a);
        $cb = $this->compare($b);

        return $ca === Comparison\Order::EQUAL || $ca !== $cb;
    }

    /**
     * Returns true if this instance represents a time duration longer than $a but
     * shorter than $b, or vice-versa (shorter than $a but longer than $b).
     * Returns false if this instance is equal to $a and/or $b, or shorter/longer
     * than both.
     */
    public function betweenExclusive(self $a, self $b): bool
    {
        $ca = $this->compare($a);
        $cb = $this->compare($b);

        return $ca !== Comparison\Order::EQUAL && $cb !== Comparison\Order::EQUAL && $ca !== $cb;
    }

    /**
     * Returns a new instance, converting a positive/negative duration to the
     * opposite (negative/positive) duration of equal length. The resulting
     * instance has all parts equivalent to the current instance's parts
     * multiplied by -1.
     */
    public function invert(): self
    {
        if ($this->isZero()) {
            return $this;
        }

        return new self(-$this->hours, -$this->minutes, -$this->seconds, -$this->nanoseconds);
    }

    /**
     * Returns a new instance representing the sum of this instance and the
     * provided `$other` instance. Note that time duration can be negative, so
     * the resulting instance is not guaranteed to be shorter/longer than either
     * of the inputs.
     *
     * This operation is commutative: `$a->plus($b) === $b->plus($a)`
     */
    public function plus(self $other): self
    {
        if ($other->isZero()) {
            return $this;
        }

        if ($this->isZero()) {
            return $other;
        }

        return self::fromParts(
            $this->hours + $other->hours,
            $this->minutes + $other->minutes,
            $this->seconds + $other->seconds,
            $this->nanoseconds + $other->nanoseconds,
        );
    }

    /**
     * Returns a new instance representing the difference between this instance
     * and the provided `$other` instance (i.e. `$other` subtracted from `$this`).
     * Note that time duration can be negative, so the resulting instance is not
     * guaranteed to be shorter/longer than either of the inputs.
     *
     * This operation is not commutative: `$a->minus($b) !== $b->minus($a)`
     * But: `$a->minus($b) === $b->minus($a)->invert()`
     */
    public function minus(self $other): self
    {
        if ($other->isZero()) {
            return $this;
        }

        if ($this->isZero()) {
            return $other->invert();
        }

        return self::fromParts(
            $this->hours - $other->hours,
            $this->minutes - $other->minutes,
            $this->seconds - $other->seconds,
            $this->nanoseconds - $other->nanoseconds,
        );
    }

    /**
     * Returns the time duration as string, useful e.g. for debugging. This is not
     * meant to be a comprehensive way to format time durations for user-facing
     * output.
     *
     * @param int<0, max> $max_decimals
     */
    public function toString(int $max_decimals = 3): string
    {
        $decimalPart = '';

        if ($max_decimals > 0) {
            $decimalPart = (string) abs($this->nanoseconds);
            $decimalPart = Str\pad_left($decimalPart, 9, '0');
            $decimalPart = Str\slice($decimalPart, 0, $max_decimals);
            $decimalPart = mb_rtrim($decimalPart, '0');
        }

        if ($decimalPart !== '') {
            $decimalPart = '.' . $decimalPart;
        }

        $secSign = $this->seconds < 0 || $this->nanoseconds < 0 ? '-' : '';
        $sec = abs($this->seconds);

        $containsHours = $this->hours !== 0;
        $containsMinutes = $this->minutes !== 0;
        $concatenatedSeconds = $secSign . $sec . $decimalPart;
        $containsSeconds = $concatenatedSeconds !== '0';

        /** @var list<string> $output */
        $output = [];

        if ($containsHours) {
            $output[] = $this->hours . ' hour(s)';
        }

        if ($containsMinutes || $containsHours && $containsSeconds) {
            $output[] = $this->minutes . ' minute(s)';
        }

        if ($containsSeconds) {
            $output[] = $concatenatedSeconds . ' second(s)';
        }

        return [] === $output ? '0 second(s)' : implode(', ', $output);
    }

    /**
     * Returns a string representation of the time duration.
     */
    #[\Override]
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Returns data which can be serialized by json_encode().
     *
     * @return array{hours: int, minutes: int, seconds: int, nanoseconds: int}
     */
    #[\Override]
    public function jsonSerialize(): array
    {
        return [
            'hours' => $this->hours,
            'minutes' => $this->minutes,
            'seconds' => $this->seconds,
            'nanoseconds' => $this->nanoseconds,
        ];
    }
}
