<?php

declare(strict_types=1);

namespace Tempest\Support;

use ArrayAccess;
use Closure;
use Countable;
use Generator;
use InvalidArgumentException;
use Iterator;
use Random\Randomizer;
use Serializable;
use Stringable;
use function Tempest\map;

/**
 * @template TKey of array-key
 * @template TValue
 *
 * @implements ArrayAccess<TKey, TValue>
 * @implements Iterator<TKey, TValue>
 */
final class ArrayHelper implements Iterator, ArrayAccess, Serializable, Countable
{
    use IsIterable;

    /**
     * The underlying array.
     *
     * @var array<TKey, TValue>
     */
    private array $array;

    /**
     * @param array<TKey, TValue>|self<TKey, TValue>|TValue $input
     */
    public function __construct(
        mixed $input = [],
    ) {
        if (is_array($input)) {
            $this->array = $input;
        } elseif ($input instanceof self) {
            $this->array = $input->array;
        } else {
            $this->array = [$input];
        }
    }

    /**
     * Get a value from the array and remove it.
     *
     * @param array-key $key
     */
    public function pull(string|int $key, mixed $default = null): mixed
    {
        $value = $this->get($key, $default);

        $this->remove($key);

        return $value;
    }

    /**
     * Shuffle the array.
     *
     * @return self<TKey, TValue>
     */
    public function shuffle(): self
    {
        return new self((new Randomizer())->shuffleArray($this->array));
    }

    /**
     * @alias of remove.
     */
    public function forget(string|int|array $keys): self
    {
        return $this->remove($keys);
    }

    /**
     * Remove items from the array.
     *
     * @param array-key|array<array-key> $keys The keys of the items to remove.
     *
     * @return self<TKey, TValue>
     */
    public function remove(string|int|array $keys): self
    {
        $keys = is_array($keys) ? $keys : [$keys];

        foreach ($keys as $key) {
            $this->offsetUnset($key);
        }

        return $this;
    }

    /**
     * Determines if the array is a list.
     *
     * An array is a list if its keys consist of consecutive numbers.
     */
    public function isList(): bool
    {
        return array_is_list($this->array);
    }

    /**
     * Determines if the array is an associative.
     *
     * An array is associative if its keys doesn't consist of consecutive numbers.
     */
    public function isAssoc(): bool
    {
        return ! $this->isList();
    }

    /**
     * Get one or a specified number of random values from the array.
     *
     * @param int $number The number of random values to get.
     * @param bool $preserveKey Whether to preserve the keys of the original array. ( won't work if $number is 1 as it will return a single value )
     *
     * @return self<TKey, TValue>|mixed The random values or single value if $number is 1.
     */
    public function random(int $number = 1, bool $preserveKey = false): mixed
    {
        $count = count($this->array);

        if ($number > $count) {
            throw new InvalidArgumentException("Cannot retrive {$number} items from an array of {$count} items.");
        }

        if ($number < 1) {
            throw new InvalidArgumentException("Random value only accepts positive integers, {$number} requested.");
        }

        $keys = (new Randomizer())->pickArrayKeys($this->array, $number);

        $randomValues = [];
        foreach ($keys as $key) {
            $preserveKey
                ? $randomValues[$key] = $this->array[$key]
                : $randomValues[] = $this->array[$key];
        }

        if ($preserveKey === false) {
            shuffle($randomValues);
        }

        return count($randomValues) > 1
            ? new self($randomValues)
            : $randomValues[0];
    }

    /**
     * Retrieve values from a given key in each sub-array of the current array.
     * Optionally, you can pass a second parameter to also get the keys following the same pattern.
     *
     * @param string $value The key to assign the values from, support dot notation.
     * @param string|null $key The key to assign the keys from, support dot notation.
     *
     * @return self<TKey, TValue>
     */
    public function pluck(string $value, ?string $key = null): self
    {
        $results = [];

        foreach ($this->array as $item) {
            if (! is_array($item)) {
                continue;
            }

            $itemValue = arr($item)->get($value);

            /**
             * Perform basic pluck if no key is given.
             * Otherwise, also pluck the key as well.
             */
            if (is_null($key)) {
                $results[] = $itemValue;
            } else {
                $itemKey = arr($item)->get($key);
                $results[$itemKey] = $itemValue;
            }
        }

        return new self($results);
    }

    /**
     * @alias of add.
     */
    public function push(mixed $value): self
    {
        return $this->add($value);
    }

    /**
     * Add an item at the end of the array.
     *
     *
     * @return self<TKey, TValue>
     */
    public function add(mixed $value): self
    {
        $this->array[] = $value;

        return $this;
    }

    /**
     * Pad the array to the specified size with a value.
     *
     *
     * @return self<TKey, TValue>
     */
    public function pad(int $size, mixed $value): self
    {
        return new self(array_pad($this->array, $size, $value));
    }

    /**
     * Reverse the keys and values of the array.
     *
     * @return self<TValue&array-key, TKey>
     */
    public function flip(): self
    {
        return new self(array_flip($this->array));
    }

    /**
     * Keep only the unique items in the array.
     *
     * @param string|null $key The key to use as the uniqueness criteria in nested arrays.
     * @param bool $should_be_strict Whether the comparison should be strict, only used when giving a key parameter.
     *
     * @return self<TKey, TValue>
     */
    public function unique(?string $key = null, bool $should_be_strict = false): self
    {
        if (is_null($key) && $should_be_strict === false) {
            return new self(array_unique($this->array, flags: SORT_REGULAR));
        }

        $uniqueItems = [];
        $uniqueFilteredValues = [];
        foreach ($this->array as $item) {
            // Ensure we don't check raw values with key filter
            if (! is_null($key) && ! is_array($item)) {
                continue;
            }

            $filterValue = is_array($item)
                ? arr($item)->get($key)
                : $item;

            if (is_null($filterValue)) {
                continue;
            }

            if (in_array($filterValue, $uniqueFilteredValues, strict: $should_be_strict)) {
                continue;
            }

            $uniqueItems[] = $item;
            $uniqueFilteredValues[] = $filterValue;
        }

        return new self($uniqueItems);
    }

    /**
     * Keep only the items that are not present in any of the given arrays.
     *
     * @param array<TKey, TValue>|self<TKey, TValue> ...$arrays
     *
     * @return self<TKey, TValue>
     */
    public function diff(array|self ...$arrays): self
    {
        $arrays = array_map(fn (array|self $array) => $array instanceof self ? $array->toArray() : $array, $arrays);

        return new self(array_diff($this->array, ...$arrays));
    }

    /**
     * Keep only the items whose keys are not present in any of the given arrays.
     *
     * @param array<TKey, TValue>|self<TKey, TValue> ...$arrays
     *
     * @return self<TKey, TValue>
     */
    public function diffKeys(array|self ...$arrays): self
    {
        $arrays = array_map(fn (array|self $array) => $array instanceof self ? $array->toArray() : $array, $arrays);

        return new self(array_diff_key($this->array, ...$arrays));
    }

    /**
     * Keep only the items that are present in all of the given arrays.
     *
     * @param array<TKey, TValue>|self<TKey, TValue> ...$arrays
     *
     * @return self<TKey, TValue>
     */
    public function intersect(array|self ...$arrays): self
    {
        $arrays = array_map(fn (array|self $array) => $array instanceof self ? $array->toArray() : $array, $arrays);

        return new self(array_intersect($this->array, ...$arrays));
    }

    /**
     * Keep only the items whose keys are present in all of the given arrays.
     *
     * @param array<TKey, TValue>|self<TKey, TValue> ...$arrays
     *
     * @return self<TKey, TValue>
     */
    public function intersectKeys(array|self ...$arrays): self
    {
        $arrays = array_map(fn (array|self $array) => $array instanceof self ? $array->toArray() : $array, $arrays);

        return new self(array_intersect_key($this->array, ...$arrays));
    }

    /**
     * Merge the array with the given arrays.
     *
     * @param array<TKey, TValue>|self<TKey, TValue> ...$arrays The arrays to merge.
     *
     * @return self<TKey, TValue>
     */
    public function merge(array|self ...$arrays): self
    {
        $arrays = array_map(fn (array|self $array) => $array instanceof self ? $array->toArray() : $array, $arrays);

        return new self(array_merge($this->array, ...$arrays));
    }

    /**
     * Create a new array with this current array values as keys and the given values as values.
     *
     * @template TCombineValue
     *
     * @param array<array-key, TCombineValue>|self<array-key, TCombineValue> $values
     *
     * @return self<array-key, TCombineValue>
     */
    public function combine(array|self $values): self
    {
        $values = $values instanceof self
            ? $values->toArray()
            : $values;

        return new self(array_combine($this->array, $values));
    }

    public static function explode(string|Stringable $string, string $separator = ' '): self
    {
        if ($separator === '') {
            return new self([(string) $string]);
        }

        return new self(explode($separator, (string) $string));
    }

    public function equals(array|self $other): bool
    {
        $other = is_array($other) ? $other : $other->array;

        return $this->array === $other;
    }

    /** @param Closure(mixed $value, mixed $key): bool $filter */
    public function first(?Closure $filter = null): mixed
    {
        if ($filter === null) {
            return $this->array[array_key_first($this->array)];
        }

        foreach ($this as $key => $value) {
            if ($filter($value, $key)) {
                return $value;
            }
        }

        return null;
    }

    /** @param Closure(mixed $value, mixed $key): bool $filter */
    public function last(?Closure $filter = null): mixed
    {
        if ($filter === null) {
            return $this->array[array_key_last($this->array)];
        }

        foreach ($this->reverse() as $key => $value) {
            if ($filter($value, $key)) {
                return $value;
            }
        }

        return null;
    }

    /** @param mixed $value The popped value will be stored in this variable */
    public function pop(mixed &$value): self
    {
        $value = $this->last();

        return new self(array_slice($this->array, 0, -1));
    }

    /** @param mixed $value The unshifted value will be stored in this variable */
    public function unshift(mixed &$value): self
    {
        $value = $this->first();

        return new self(array_slice($this->array, 1));
    }

    public function reverse(): self
    {
        return new self(array_reverse($this->array));
    }

    public function isEmpty(): bool
    {
        return empty($this->array);
    }

    public function isNotEmpty(): bool
    {
        return ! $this->isEmpty();
    }

    public function implode(string $glue): StringHelper
    {
        return str(implode($glue, $this->array));
    }

    /**
     * Create a new array with the keys of this array as values.
     *
     * @return self<array-key, TKey>
     */
    public function keys(): self
    {
        return new self(array_keys($this->array));
    }

    public function values(): self
    {
        return new self(array_values($this->array));
    }

    /** @param null|Closure(mixed $value, mixed $key): bool $filter */
    public function filter(?Closure $filter = null): self
    {
        $array = [];
        $filter ??= static fn (mixed $value, mixed $_) => ! in_array($value, [false, null], strict: true);

        foreach ($this->array as $key => $value) {
            if ($filter($value, $key)) {
                $array[$key] = $value;
            }
        }

        return new self($array);
    }

    /** @param Closure(mixed $value, mixed $key): void $each */
    public function each(Closure $each): self
    {
        foreach ($this as $key => $value) {
            $each($value, $key);
        }

        return $this;
    }

    /** @param Closure(mixed $value, mixed $key): mixed $map */
    public function map(Closure $map): self
    {
        $array = [];

        foreach ($this->array as $key => $value) {
            $array[$key] = $map($value, $key);
        }

        return new self($array);
    }

    /** @param Closure(mixed $value, mixed $key): Generator $map */
    public function mapWithKeys(Closure $map): self
    {
        $array = [];

        foreach ($this->array as $key => $value) {
            $generator = $map($value, $key);

            if (! $generator instanceof Generator) {
                throw new InvalidMapWithKeysUsage();
            }

            $array[$generator->key()] = $generator->current();
        }

        return new self($array);
    }

    /** @return mixed|ArrayHelper */
    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->array;

        $keys = explode('.', $key);

        foreach ($keys as $key) {
            if (! isset($value[$key])) {
                return $default;
            }

            $value = $value[$key];
        }

        if (is_array($value)) {
            return new self($value);
        }

        return $value;
    }

    public function has(string $key): bool
    {
        $array = $this->array;

        $keys = explode('.', $key);

        foreach ($keys as $key) {
            if (! isset($array[$key])) {
                return false;
            }

            $array = &$array[$key];
        }

        return true;
    }

    public function contains(mixed $search): bool
    {
        return $this->first(fn ($value) => $value === $search) !== null;
    }

    public function set(string $key, mixed $value): self
    {
        $array = $this->array;

        $current = &$array;

        $keys = explode('.', $key);

        foreach ($keys as $i => $key) {
            // If this is the last key in dot notation, we don't
            // need to go through the next steps.
            if (count($keys) === 1) {
                break;
            }

            // Remove the current key from our keys array
            // so that later we can use the first value
            // from that array as our key.
            unset($keys[$i]);

            // If we know this key is not an array, make it one.
            if (! isset($current[$key]) || ! is_array($current[$key])) {
                $current[$key] = [];
            }

            // Set the context to this key.
            $current = &$current[$key];
        }

        // Pull the first key out of the array
        // and use it to set the value.
        $current[array_shift($keys)] = $value;

        return new self($array);
    }

    /**
     * @alias self::set()
     */
    public function put(string $key, mixed $value): self
    {
        return $this->set($key, $value);
    }

    public function unwrap(): self
    {
        $unwrapValue = function (string|int $key, mixed $value) {
            if (is_int($key)) {
                return [$key => $value];
            }

            $keys = explode('.', $key);

            for ($i = array_key_last($keys); $i >= 0; $i--) {
                $currentKey = $keys[$i];

                $value = [$currentKey => $value];
            }

            return $value;
        };

        $array = [];

        foreach ($this->array as $key => $value) {
            $array = array_merge_recursive($array, $unwrapValue($key, $value));
        }

        return new self($array);
    }

    public function dump(mixed ...$dumps): self
    {
        lw($this->array, ...$dumps);

        return $this;
    }

    public function dd(mixed ...$dd): void
    {
        ld($this->array, ...$dd);
    }

    /**
     * @return array<TKey, TValue>
     */
    public function toArray(): array
    {
        return $this->array;
    }

    /**
     * @template T
     * @param class-string<T> $to
     * @return self<T>
     */
    public function mapTo(string $to): self
    {
        return new self(map($this->array)->collection()->to($to));
    }
}
