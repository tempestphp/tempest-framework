<?php

declare(strict_types=1);

namespace Tempest\Support;

use ArrayIterator;
use BackedEnum;
use InvalidArgumentException;
use Iterator;
use IteratorAggregate;
use Tempest\Support\Arr\ImmutableArray;
use UnitEnum;
use ValueError;
use function Tempest\Support\Arr\to_array;

/**
 * This trait provides a bunch of helper methods to work with enums
 *
 * @template TEnum of \UnitEnum
 */
trait IsEnumHelper
{
    /**
     * Gets the enum case by name
     *
     * @throws ValueError if the case name is not valid
     */
    public static function fromName(string $name): static
    {
        return static::tryFromName($name) ?? throw new ValueError(sprintf('"%s" is not a valid case name for enum "%s"', $name, static::class));
    }

    /**
     * Gets the enum case by name or null if the case name is not valid
     */
    public static function tryFromName(string $name): ?static
    {
        $cases = array_filter(
            static::cases(),
            fn (UnitEnum $case) => $case->name === $name,
        );

        return array_shift($cases);
    }

    /**
     * Gets the enum case by name, for Pure enums
     * This will not override the `from()` method for Backed enums
     */
    public static function from(string $case): static
    {
        return static::fromName($case);
    }

    /**
     * Gets the enum case by name, for Pure enums or null if the case is not valid
     * This will not override the `tryFrom()` method for Backed enums
     */
    public static function tryFrom(string $case): ?static
    {
        return static::tryFromName($case);
    }

    /**
     * Check if the current enum case is equal to the given enum
     *
     * @param UnitEnum $enum The enum to compare
     *
     * @return bool True if the enums are equal, false otherwise
     */
    public function is(UnitEnum $enum): bool
    {
        return $this === $enum;
    }

    /**
     * Check if the current enum case is not equal to the given enum
     *
     * @param UnitEnum $enum The enum to compare
     *
     * @return bool True if the enums are not equal, false otherwise
     */
    public function isNot(UnitEnum $enum): bool
    {
        return ! $this->is($enum);
    }

    /**
     * Check if the current enum case is in the given list of enums
     *
     * @param iterable<UnitEnum> $enums The list of enums to check
     *
     * @return bool True if the current enum is in the list, false otherwise
     */
    public function in(iterable $enums): bool
    {
        $iterator = match (true) {
            is_array($enums) => new ArrayIterator($enums),
            $enums instanceof Iterator => $enums,
            $enums instanceof IteratorAggregate => $enums->getIterator(),
            default => throw new InvalidArgumentException(sprintf('The given value must be an iterable value, "%s" given', get_debug_type($enums))),
        };

		return array_any(to_array($iterator), fn($enum) => $this->is($enum));
	}

    /**
     * Check if the current enum case is not in the given list of enums
     *
     * @param iterable<UnitEnum> $enums The list of enums to check
     *
     * @return bool True if the current enum is not in the list, false otherwise
     */
    public function notIn(iterable $enums): bool
    {
        return ! $this->in($enums);
    }

    /**
     * Check if the current enum has the name in its cases
     *
     * @param string $name The enum case name
     *
     * @return bool True if the name is in the enum, false otherwise
     */
    public static function has(string $name): bool
    {
        $caseNames = array_column(static::cases(), 'name');

        return in_array($name, $caseNames, strict: true); /** @phpstan-ignore-line function.impossibleType ( prevent to always evaluate to true/false as in enum context the result is predictable ) */
    }

    /**
     * Check if the current enum does not have the name in its cases
     *
     * @param string $name The enum case name
     *
     * @return bool True if the name is not in the enum, false otherwise
     */
    public static function hasNot(string $name): bool
    {
        return ! self::has($name);
    }

    /**
     * Check if the current enum has the value in its cases
     * For Pure enums, this method is the equivalent of `has()`
     *
     * @param string|int $value The value to check
     *
     * @return bool True if the value is in the enum, false otherwise
     */
    public static function hasValue(string|int $value): bool
    {
        /** @var class-string<TEnum> */
        $class = static::class;

        $caseValues = is_subclass_of($class, BackedEnum::class)
            ? array_column(static::cases(), 'value')
            : array_column(static::cases(), 'name');

        return in_array($value, $caseValues, strict: true);  /** @phpstan-ignore-line function.impossibleType ( prevent to always evaluate to true/false as in enum context the result is predictable ) */
    }

    /**
     * Check if the current enum does not have the value in its cases
     * For Pure enums, this method is the equivalent of `hasNot()`
     *
     * @param string|int $value The value to check
     *
     * @return bool True if the value is not in the enum, false otherwise
     */
    public static function hasNotValue(string|int $value): bool
    {
        return ! self::hasValue($value);
    }

    /**
     * Gets an array of case names
     * Both pure and backed enums will return their names
     *
     * @return array<int, string>
     */
    public static function names(): array
    {
        return array_column(static::cases(), 'name');
    }

    /**
     * Gets an array of case values
     * Pure enums will return their names, backed enums will return their values
     *
     * @return array<int, int|string>
     */
    public static function values(): array
    {
        /** @var class-string<TEnum> */
        $class = static::class;

        return is_subclass_of($class, BackedEnum::class)
            ? array_column(static::cases(), 'value')
            : array_column(static::cases(), 'name');
    }

    /**
     * Wrap all enum cases in an array
     *
     * @return ImmutableArray<static>
     */
    public static function collect(): ImmutableArray
    {
        return arr(static::cases());
    }

    /**
     * Returns an associative array of case names and values
     * For pure enums, this method is the equivalent of `values()`
     *
     * @return array<int|string, int|string>
     */
    public static function options(): array
    {
        /** @var class-string<TEnum> */
        $class = static::class;

        return is_subclass_of($class, BackedEnum::class)
            ? array_column(static::cases(), 'value', 'name')
            : self::values();
    }
}
