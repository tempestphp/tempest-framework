<?php

declare(strict_types=1);

namespace Tempest\Support\Arr {
    use Closure;
    use Countable;
    use Generator;
    use InvalidArgumentException;
    use Random\Randomizer;
    use Tempest\Support\Str\ImmutableString;
    use Traversable;

    use function sort as php_sort;

    /**
     * Finds a value in the array and return the corresponding key if successful.
     *
     * @template TKey of array-key
     * @template TValue
     *
     * @param iterable<TKey,TValue> $array
     * @param (Closure(TValue, TKey): bool)|mixed $value The value to search for, a {@see Closure} will find the first item that returns true.
     * @param bool $strict Whether to use strict comparison.
     *
     * @return array-key|null The key for `$value` if found, `null` otherwise.
     */
    function find_key(iterable $array, mixed $value, bool $strict = false): int|string|null
    {
        $array = to_array($array);

        if (! ($value instanceof Closure)) {
            $search = array_search($value, $array, $strict); // @mago-expect strictness/require-strict-behavior

            return $search === false ? null : $search; // Keep empty values but convert false to null
        }

        return array_find_key($array, static fn ($item, $key) => $value($item, $key) === true);
    }

    /**
     * Chunks the array into chunks of the given size.
     *
     * @template TKey of array-key
     * @template TValue
     *
     * @param iterable<TKey,TValue> $array
     * @param int $size The size of each chunk.
     * @param bool $preserveKeys Whether to preserve the keys of the original array.
     *
     * @return array<int,array<TKey, TValue>>
     */
    function chunk(iterable $array, int $size, bool $preserveKeys = true): array
    {
        $array = to_array($array);

        if ($size <= 0) {
            return [];
        }

        $chunks = [];
        foreach (array_chunk($array, $size, $preserveKeys) as $chunk) {
            $chunks[] = $chunk;
        }

        return $chunks;
    }

    /**
     * Reduces the array to a single value using a callback.
     *
     * @template TKey of array-key
     * @template TValue
     *
     * @template TReduceInitial
     * @template TReduceReturnType
     *
     * @param iterable<TKey,TValue> $array
     * @param callable(TReduceInitial|TReduceReturnType, TValue, TKey): TReduceReturnType $callback
     * @param TReduceInitial $initial
     *
     * @return TReduceReturnType
     */
    function reduce(iterable $array, callable $callback, mixed $initial = null): mixed
    {
        $array = to_array($array);

        $result = $initial;

        foreach ($array as $key => $value) {
            $result = $callback($result, $value, $key);
        }

        return $result;
    }

    /**
     * Gets a value from the array and remove it.
     *
     * @template TKey of array-key
     * @template TValue
     *
     * @param array<TKey,TValue> $array
     * @param array-key $key
     */
    function pull(array &$array, string|int $key, mixed $default = null): mixed
    {
        $value = get_by_key($array, $key, $default);
        $array = namespace\remove($array, $key);

        return $value;
    }

    /**
     * Shuffles the array.
     *
     * @template TKey of array-key
     * @template TValue
     *
     * @param iterable<TKey,TValue> $array
     * @return array<TKey, TValue>
     */
    function shuffle(iterable $array): array
    {
        return new Randomizer()->shuffleArray(to_array($array));
    }

    /**
     * Alias of {@see \Tempest\Support\Arr\remove}.
     */
    function forget(iterable $array, string|int|array $keys): array
    {
        return namespace\remove(to_array($array), $keys);
    }

    /**
     * Removes the specified items from the array.
     *
     * @template TKey of array-key
     * @template TValue
     *
     * @param array<TKey,TValue> $array
     * @param array-key|array<array-key> $keys The keys of the items to remove.
     * @return array<TKey,TValue>
     */
    function remove(array $array, string|int|array $keys): array
    {
        $keys = is_array($keys) ? $keys : [$keys];

        foreach ($keys as $key) {
            unset($array[$key]);
        }

        return $array;
    }

    /**
     * Asserts whether the array is a list.
     * An array is a list if its keys consist of consecutive numbers.
     */
    function is_list(iterable $array): bool
    {
        return array_is_list(to_array($array));
    }

    /**
     * Asserts whether the array is a associative.
     * An array is associative if its keys do not consist of consecutive numbers.
     */
    function is_associative(iterable $array): bool
    {
        return ! is_list(to_array($array));
    }

    /**
     * Gets one or a specified number of random values from the array.
     *
     * @template TKey of array-key
     * @template TValue
     *
     * @param iterable<TKey,TValue> $array
     * @param int $number The number of random values to get.
     * @param bool $preserveKey Whether to include the keys of the original array.
     *
     * @return array<TKey, TValue>|mixed The random values, or a single value if `$number` is 1.
     */
    function random(iterable $array, int $number = 1, bool $preserveKey = false): mixed
    {
        $array = to_array($array);

        $count = count($array);

        if ($number > $count) {
            throw new InvalidArgumentException("Cannot retrieve {$number} items from an array of {$count} items.");
        }

        if ($number < 1) {
            throw new InvalidArgumentException("Random value only accepts positive integers, {$number} requested.");
        }

        $keys = new Randomizer()->pickArrayKeys($array, $number);

        $randomValues = [];
        foreach ($keys as $key) {
            $preserveKey
                ? ($randomValues[$key] = $array[$key])
                : ($randomValues[] = $array[$key]);
        }

        if ($preserveKey === false) {
            shuffle($randomValues);
        }

        return count($randomValues) > 1
            ? new ImmutableArray($randomValues)
            : $randomValues[0];
    }

    /**
     * Retrieves values from a given key in each sub-array of the current array.
     * Optionally, you can pass a second parameter to also get the keys following the same pattern.
     *
     * @param string $value The key to assign the values from, support dot notation.
     * @param string|null $key The key to assign the keys from, support dot notation.
     */
    function pluck(iterable $array, string $value, ?string $key = null): array
    {
        $array = to_array($array);

        $results = [];

        foreach ($array as $item) {
            if (! is_array($item)) {
                continue;
            }

            $itemValue = get_by_key($item, $value);

            /**
             * Perform basic pluck if no key is given.
             * Otherwise, also pluck the key as well.
             */
            if (is_null($key)) {
                $results[] = $itemValue;
            } else {
                $itemKey = get_by_key($item, $key);
                $results[$itemKey] = $itemValue;
            }
        }

        return $results;
    }

    /**
     * Returns a new array with the specified values prepended.
     *
     * @template TKey of array-key
     * @template TValue
     *
     * @param iterable<TKey,TValue> $array
     * @param TValue $values
     */
    function prepend(iterable $array, mixed ...$values): array
    {
        $array = to_array($array);

        foreach (array_reverse($values) as $value) {
            $array = [$value, ...$array];
        }

        return $array;
    }

    /**
     * Appends the specified values to the array.
     *
     * @template TKey of array-key
     * @template TValue
     *
     * @param iterable<TKey,TValue> $array
     * @param TValue $values
     */
    function append(iterable $array, mixed ...$values): array
    {
        $array = to_array($array);

        foreach ($values as $value) {
            $array = [...$array, $value];
        }

        return $array;
    }

    /**
     * Appends the specified value to the array.
     *
     * @template TKey of array-key
     * @template TValue
     *
     * @param iterable<TKey,TValue> $array
     * @param TValue $value
     */
    function push(iterable $array, mixed $value): array
    {
        $array = to_array($array);
        $array[] = $value;

        return $array;
    }

    /**
     * Pads the array to the specified size with a value.
     */
    function pad(iterable $array, int $size, mixed $value): array
    {
        $array = to_array($array);

        return array_pad($array, $size, $value);
    }

    /**
     * Reverses the keys and values of the array.
     *
     * @template TKey of array-key
     * @template TValue
     *
     * @param iterable<TKey,TValue> $array
     * @return array<TValue&array-key, TKey>
     */
    function flip(iterable $array): array
    {
        $array = to_array($array);

        return array_flip($array);
    }

    /**
     * Returns a new array with only unique items from the original array.
     *
     * @param string|null|Closure $key The key to use as the uniqueness criteria in nested arrays.
     * @param bool $shouldBeStrict Whether the comparison should be strict, only used when giving a key parameter.
     */
    function unique(iterable $array, null|Closure|string $key = null, bool $shouldBeStrict = false): array
    {
        $array = to_array($array);

        if (is_null($key) && $shouldBeStrict === false) {
            return array_unique($array, flags: SORT_REGULAR);
        }

        $uniqueItems = [];
        $uniqueFilteredValues = [];

        foreach ($array as $item) {
            // Ensure we don't check raw values with key filter
            if (! is_null($key) && ! is_array($item) && ! ($key instanceof Closure)) {
                continue;
            }

            $filterValue = match ($key instanceof Closure) {
                true => $key($item, $array),
                false => is_array($item)
                    ? get_by_key($item, $key)
                    : $item,
            };

            if (is_null($filterValue)) {
                continue;
            }

            if (in_array($filterValue, $uniqueFilteredValues, strict: $shouldBeStrict)) { // @mago-expect strictness/require-strict-behavior
                continue;
            }

            $uniqueItems[] = $item;
            $uniqueFilteredValues[] = $filterValue;
        }

        return $uniqueItems;
    }

    /**
     * Returns a copy of the given array with only the items that are not present in any of the given arrays.
     *
     * @template TKey of array-key
     * @template TValue
     *
     * @param iterable<TKey,TValue> $array
     * @param array<TKey, TValue> ...$arrays
     */
    function diff(iterable $array, array ...$arrays): array
    {
        return array_diff(to_array($array), ...$arrays);
    }

    /**
     * Returns a new array with only the items whose keys are not present in any of the given arrays.
     *
     * @template TKey of array-key
     * @template TValue
     *
     * @param iterable<TKey,TValue> $array
     * @param array<TKey, TValue> ...$arrays
     */
    function diff_keys(iterable $array, array ...$arrays): array
    {
        return array_diff_key(to_array($array), ...$arrays);
    }

    /**
     * Returns a copy of the given array with only the items that are present in all of the given arrays.
     *
     * @template TKey of array-key
     * @template TValue
     *
     * @param iterable<TKey,TValue> $array
     * @param array<TKey, TValue> ...$arrays
     */
    function intersect(iterable $array, array ...$arrays): array
    {
        return array_intersect(to_array($array), ...$arrays);
    }

    /**
     * Returns a copy of the given array with only the items whose keys are present in all of the given arrays.
     *
     * @template TKey of array-key
     * @template TValue
     *
     * @param iterable<TKey,TValue> $array
     * @param array<TKey, TValue> ...$arrays
     */
    function intersect_keys(iterable $array, array ...$arrays): array
    {
        return array_intersect_key(to_array($array), ...$arrays);
    }

    /**
     * Merges the array with the given arrays.
     *
     * @template TKey of array-key
     * @template TValue
     *
     * @param iterable<TKey,TValue> $array
     * @param array<TKey, TValue> ...$arrays The arrays to merge.
     */
    function merge(iterable $array, iterable ...$arrays): array
    {
        return array_merge(to_array($array), ...array_map(to_array(...), $arrays));
    }

    /**
     * Creates a new array with this current array values as keys and the given values as values.
     *
     * @template TCombineValue
     * @template TKey of array-key
     * @template TValue
     *
     * @param iterable<TKey,TValue> $array
     * @param iterable<array-key, TCombineValue> $values
     *
     * @return array<array-key, TCombineValue>
     */
    function combine(iterable $array, iterable $values): array
    {
        $array = to_array($array);
        $values = to_array($values);

        return array_combine($array, $values);
    }

    /**
     * Asserts whether the given `$array` is equal to `$other` array.
     */
    function equals(iterable $array, iterable $other): bool
    {
        $array = to_array($array);
        $other = to_array($other);

        return $array === $other;
    }

    /**
     * Returns the first item in the array that matches the given `$filter`.
     * If `$filter` is `null`, returns the first item.
     *
     * @template TKey of array-key
     * @template TValue
     *
     * @param iterable<TKey,TValue> $array
     * @param null|Closure(TValue $value, TKey $key): bool $filter
     *
     * @return TValue
     */
    function first(iterable $array, ?Closure $filter = null, mixed $default = null): mixed
    {
        $array = to_array($array);

        if ($array === []) {
            return $default;
        }

        if ($filter === null) {
            return $array[array_key_first($array)] ?? $default;
        }

        return array_find($array, static fn ($value, $key) => $filter($value, $key)) ?? $default;
    }

    /**
     * Returns the item at the given index in the specified array.
     *
     * @template TKey of array-key
     * @template TValue
     *
     * @param iterable<TKey,TValue> $array
     *
     * @return TValue
     */
    function at(iterable $array, int $index, mixed $default = null): mixed
    {
        $array = to_array($array);

        if ($index < 0) {
            $index = abs($index) - 1;
            $array = namespace\reverse($array);
        }

        return namespace\get_by_key(array_values($array), key: $index, default: $default);
    }

    /**
     * Returns the last item in the array that matches the given `$filter`.
     * If `$filter` is `null`, returns the last item.
     *
     * @template TKey of array-key
     * @template TValue
     *
     * @param iterable<TKey,TValue> $array
     * @param null|Closure(TValue $value, TKey $key): bool $filter
     *
     * @return TValue
     */
    function last(iterable $array, ?Closure $filter = null, mixed $default = null): mixed
    {
        $array = to_array($array);

        if ($array === []) {
            return $default;
        }

        if ($filter === null) {
            return $array[array_key_last($array)] ?? $default;
        }

        return array_find(namespace\reverse($array), static fn ($value, $key) => $filter($value, $key)) ?? $default;
    }

    /**
     * Returns a copy of the given array without the last value.
     *
     * @param mixed $value The popped value will be stored in this variable.
     */
    function pop(iterable $array, mixed &$value = null): array
    {
        $array = to_array($array);
        $value = namespace\last($array);

        return array_slice($array, 0, -1);
    }

    /**
     * Returns a copy of the given array without the first value.
     *
     * @param mixed $value The unshifted value will be stored in this variable
     */
    function unshift(iterable $array, mixed &$value = null): array
    {
        $array = to_array($array);
        $value = namespace\first($array);

        return array_slice($array, 1);
    }

    /**
     * Returns a copy of the given array in reverse order.
     */
    function reverse(iterable $array): array
    {
        return array_reverse(to_array($array));
    }

    /**
     * Asserts whether the array is empty.
     */
    function is_empty(iterable $array): bool
    {
        return to_array($array) === [];
    }

    /**
     * Returns an instance of {@see \Tempest\Support\Str\ImmutableString} with the values of the array joined with the given `$glue`.
     */
    function implode(iterable $array, string $glue): ImmutableString
    {
        return new ImmutableString(\implode($glue, to_array($array)));
    }

    /**
     * Returns a copy of the given array with the keys of this array as values.
     */
    function keys(iterable $array): array
    {
        return array_keys(to_array($array));
    }

    /**
     * Returns a copy of the given array without its keys.
     */
    function values(iterable $array): array
    {
        return array_values(to_array($array));
    }

    /**
     * Returns a copy of the given array with only the items that pass the given `$filter`.
     * If `$filter` is `null`, the new array will contain only values that are not `false` or `null`.
     *
     * @template TKey of array-key
     * @template TValue
     *
     * @param iterable<TKey,TValue> $array
     * @param null|Closure(TValue $value, TKey $key): bool $filter
     */
    function filter(iterable $array, ?Closure $filter = null): array
    {
        $result = [];
        $filter ??= static fn (mixed $value, mixed $_) => ! in_array($value, [false, null], strict: true);

        foreach (to_array($array) as $key => $value) {
            if ($filter($value, $key)) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Applies the given callback to all items of the array.
     *
     * @template TKey of array-key
     * @template TValue
     *
     * @param iterable<TKey,TValue> $array
     * @param Closure(TKey $value, TValue $key): void $each
     */
    function each(iterable $array, Closure $each): array
    {
        $array = to_array($array);

        foreach ($array as $key => $value) {
            $each($value, $key);
        }

        return $array;
    }

    /**
     * Returns a copy of the given array with each item transformed by the given callback.
     *
     * @template TMapValue
     *
     * @template TKey of array-key
     * @template TValue
     *
     * @param iterable<TKey,TValue> $array
     * @param Closure(TValue, TKey): TMapValue $map
     *
     * @return array<TKey, TMapValue>
     */
    function map_iterable(iterable $array, Closure $map): array
    {
        $result = [];

        foreach (to_array($array) as $key => $value) {
            $result[$key] = $map($value, $key);
        }

        return $result;
    }

    /**
     * Returns a copy of the given array with each item transformed by the given callback.
     * The callback must return a generator, associating a key and a value.
     *
     * ### Example
     * ```php
     * map_with_keys(['a', 'b'], fn (mixed $value, mixed $key) => yield $key => $value);
     * ```
     *
     * @template TKey of array-key
     * @template TValue
     *
     * @param iterable<TKey,TValue> $array
     * @param Closure(TValue $value, TKey $key): Generator $map
     */
    function map_with_keys(iterable $array, Closure $map): array
    {
        $result = [];

        foreach (to_array($array) as $key => $value) {
            $generator = $map($value, $key);

            // @phpstan-ignore instanceof.alwaysTrue
            if (! ($generator instanceof Generator)) {
                throw new InvalidMapWithKeysUsage();
            }

            $result[$generator->key()] = $generator->current();
        }

        return $result;
    }

    /**
     * Gets the value identified by the specified `$key`, or `$default` if no such value exists.
     * @return mixed|ImmutableArray
     */
    function get_by_key(iterable $array, int|string $key, mixed $default = null): mixed
    {
        $value = to_array($array);

        if (isset($value[$key])) {
            return is_array($value[$key])
                ? new ImmutableArray($value[$key])
                : $value[$key];
        }

        $keys = is_int($key)
            ? [$key]
            : explode('.', $key);

        foreach ($keys as $key) {
            if (! isset($value[$key])) {
                return $default;
            }

            $value = $value[$key];
        }

        if (is_array($value)) {
            return new ImmutableArray($value);
        }

        return $value;
    }

    /**
     * Asserts whether a value identified by the specified `$key` exists.
     */
    function has(iterable $array, int|string $key): bool
    {
        $array = to_array($array);

        if (isset($array[$key])) {
            return true;
        }

        $keys = is_int($key)
            ? [$key]
            : explode('.', $key);

        foreach ($keys as $key) {
            if (! isset($array[$key])) {
                return false;
            }

            $array = &$array[$key];
        }

        return true;
    }

    /**
     * Asserts whether the given array contains an item that can be identified by `$search`.
     */
    function contains(iterable $array, mixed $search): bool
    {
        return namespace\first(to_array($array), fn (mixed $value) => $value === $search) !== null;
    }

    /**
     * Asserts whether all items in the given array pass the given `$callback`.
     *
     * @template TKey of array-key
     * @template TValue
     *
     * @param iterable<TKey,TValue> $array
     * @param Closure(TValue, TKey): bool $callback
     *
     * @return bool If the collection is empty, returns `true`.
     */
    function every(iterable $array, ?Closure $callback = null): bool
    {
        $array = to_array($array);
        $callback ??= static fn (mixed $value) => ! is_null($value);

        return array_all($array, static fn (mixed $value, int|string $key) => $callback($value, $key));
    }

    /**
     * Returns a copy of the array with the given `$value` associated to the given `$key`.
     */
    function set_by_key(iterable $array, string $key, mixed $value): array
    {
        $array = to_array($array);
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

        return $array;
    }

    /**
     * Returns a copy of the array that converts the dot-notated keys to a set of nested arrays.
     */
    function undot(iterable $array): array
    {
        $array = to_array($array);

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

        $unwrapped = [];

        foreach ($array as $key => $value) {
            $unwrapped[] = $unwrapValue($key, $value);
        }

        return array_merge_recursive(...$unwrapped);
    }

    /**
     * Returns a copy of the array that converts nested arrays to a single-dimension dot-notation array.
     */
    function dot(iterable $array, string $prefix = ''): array
    {
        $array = to_array($array);

        $result = [];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = [...$result, ...dot($value, $prefix . $key . '.')];
            } else {
                $result[$prefix . $key] = $value;
            }
        }

        return $result;
    }

    /**
     * Joins all values using the specified `$glue`. The last item of the string is separated by `$finalGlue`.
     */
    function join(iterable $array, string $glue = ', ', ?string $finalGlue = ' and '): ImmutableString
    {
        $array = to_array($array);

        if ($finalGlue === '' || is_null($finalGlue)) {
            return namespace\implode($array, $glue);
        }

        if (namespace\is_empty($array)) {
            return new ImmutableString('');
        }

        $parts = namespace\pop($array, $last);

        if (! namespace\is_empty($parts)) {
            return namespace\implode($parts, $glue)->append($finalGlue, $last);
        }

        return new ImmutableString($last);
    }

    /**
     * Returns a copy of the array flattened to a single level, or until the specified `$depth` is reached.
     *
     * ### Example
     * ```php
     * flatten(['foo', ['bar', 'baz']]); // ['foo', 'bar', 'baz']
     * ```
     */
    function flatten(iterable $array, int|float $depth = INF): array
    {
        $array = to_array($array);
        $result = [];

        foreach ($array as $item) {
            if (! is_array($item)) {
                $result[] = $item;

                continue;
            }

            $values = $depth === 1
                ? namespace\values($item)
                : namespace\flatten($item, $depth - 1);

            foreach ($values as $value) {
                $result[] = $value;
            }
        }

        return $result;
    }

    /**
     * Returns a copy of the array grouped by the result of the given `$keyExtractor`.
     * The keys of the resulting array are the values returned by the `$keyExtractor`.
     *
     * ### Example
     * ```php
     * group_by(
     * [
     * ['country' => 'france', 'continent' => 'europe'],
     * ['country' => 'Sweden', 'continent' => 'europe'],
     * ['country' => 'USA', 'continent' => 'america']
     * ],
     * fn($item) => $item['continent']
     * );
     * // [
     * //     'europe' => [
     * //         ['country' => 'france', 'continent' => 'europe'],
     * //         ['country' => 'Sweden', 'continent' => 'europe']
     * //     ],
     * //     'america' => [
     * //         ['country' => 'USA', 'continent' => 'america']
     * //     ]
     * // ]
     * ```
     *
     * @template TKey of array-key
     * @template TValue
     * @param iterable<TKey,TValue> $array
     * @param Closure(TValue, TKey): array-key $keyExtracor
     */
    function group_by(iterable $array, Closure $keyExtracor): array
    {
        $array = to_array($array);

        $result = [];

        foreach ($array as $key => $item) {
            $key = $keyExtracor($item, $key);

            $result[$key][] = $item;
        }

        return $result;
    }

    /**
     * Returns a copy of the given array, with each item transformed by the given callback, then flattens it by the specified depth.
     *
     * @template TMapValue
     * @template TKey of array-key
     * @template TValue
     *
     * @param iterable<TKey,TValue> $array
     * @param Closure(TValue,TKey): TMapValue[] $map
     *
     * @return array<TKey,TMapValue>
     */
    function flat_map(iterable $array, Closure $map, int|float $depth = 1): array
    {
        return namespace\flatten(namespace\map_iterable(to_array($array), $map), $depth);
    }

    /**
     * Returns a new array with the value of the given array mapped to the given object.
     *
     * @see Tempest\map()
     *
     * @template T
     * @param class-string<T> $to
     */
    function map_to(iterable $array, string $to): array
    {
        return \Tempest\map(to_array($array))->collection()->to($to);
    }

    /**
     * Returns a copy of the given array sorted by its values.
     *
     * @template TKey of array-key
     * @template TValue
     *
     * @param iterable<TKey,TValue> $array
     * @param bool $desc Sorts in descending order if `true`; defaults to `false` (ascending).
     * @param bool|null $preserveKeys Preserves array keys if `true`; reindexes numerically if `false`.
     *                                Defaults to `null`, which auto-detects preservation based on array type  (associative or list).
     * @param int $flags Sorting flags to define comparison behavior, defaulting to `SORT_REGULAR`.
     * @return array<array-key, TValue> Key type depends on whether array keys are preserved or not.
     */
    function sort(iterable $array, bool $desc = false, ?bool $preserveKeys = null, int $flags = SORT_REGULAR): array
    {
        $array = to_array($array);

        if ($preserveKeys === null) {
            $preserveKeys = is_associative($array);
        }

        if ($preserveKeys) {
            $desc ? arsort($array, $flags) : asort($array, $flags);
        } else {
            $desc ? rsort($array, $flags) : php_sort($array, $flags);
        }

        return $array;
    }

    /**
     * Returns a copy of the given array sorted by its values using a callback function.
     *
     * @template TKey of array-key
     * @template TValue
     *
     * @param iterable<TKey,TValue> $array
     * @param callable $callback The function to use for comparing values. It should accept two parameters
     *                           and return an integer less than, equal to, or greater than zero if the
     *                           first argument is considered to be respectively less than, equal to, or
     *                           greater than the second.
     * @param bool|null $preserveKeys Preserves array keys if `true`; reindexes numerically if `false`.
     *                                Defaults to `null`, which auto-detects preservation based on array type  (associative or list).
     * @return array<array-key, TValue> Key type depends on whether array keys are preserved or not.
     */
    function sort_by_callback(iterable $array, callable $callback, ?bool $preserveKeys = null): array
    {
        $array = to_array($array);

        if ($preserveKeys === null) {
            $preserveKeys = is_associative($array);
        }

        $preserveKeys ? uasort($array, $callback) : usort($array, $callback);

        return $array;
    }

    /**
     * Returns a copy of the given array sorted by its keys.
     *
     * @template TKey of array-key
     * @template TValue
     *
     * @param iterable<TKey,TValue> $array
     * @param bool $desc Sorts in descending order if `true`; defaults to `false` (ascending).
     * @param int $flags Sorting flags to define comparison behavior, defaulting to `SORT_REGULAR`.
     * @return array<TKey, TValue>
     */
    function sort_keys(iterable $array, bool $desc = false, int $flags = SORT_REGULAR): array
    {
        $array = to_array($array);

        $desc ? krsort($array, $flags) : ksort($array, $flags);

        return $array;
    }

    /**
     * Returns a copy of the given array sorted by its keys using a callback function.
     *
     * @template TKey of array-key
     * @template TValue
     *
     * @param iterable<TKey,TValue> $array
     * @param callable $callback The function to use for comparing keys. It should accept two parameters
     *                           and return an integer less than, equal to, or greater than zero if the
     *                           first argument is considered to be respectively less than, equal to, or
     *                           greater than the second.
     * @return array<TKey, TValue>
     */
    function sort_keys_by_callback(iterable $array, callable $callback): array
    {
        $array = to_array($array);

        uksort($array, $callback);

        return $array;
    }

    /**
     * Extracts a part of the array.
     *
     * ### Example
     * ```php
     * slice([1, 2, 3, 4, 5], 2); // [3, 4, 5]
     * ```
     */
    function slice(iterable $array, int $offset, ?int $length = null): array
    {
        $array = to_array($array);
        $length ??= count($array) - $offset;

        return array_slice($array, $offset, $length);
    }

    /**
     * Wraps the specified `$input` into an array. If the `$input` is already an array, it is returned.
     * As opposed to {@see \Tempest\Support\Arr\to_array}, this function does not convert {@see Traversable} and {@see Countable} instances to arrays.
     */
    function wrap(mixed $input = []): array
    {
        if (is_array($input)) {
            return $input;
        }

        if ($input instanceof ArrayInterface) {
            return $input->toArray();
        }

        if ($input === null) {
            return [];
        }

        return [$input];
    }

    /**
     * Converts various data structures to a PHP array.
     * As opposed to `{@see \Tempest\Support\Arr\wrap}`, this function converts {@see Traversable} and {@see Countable} instances to arrays.
     *
     * @param mixed $input Any value that can be converted to an array:
     *                     - Arrays are returned as-is
     *                     - Scalar values are wrapped in an array
     *                     - Traversable objects are converted using `{@see iterator_to_array}`
     *                     - {@see Countable} objects are converted to arrays
     *                     - {@see null} becomes an empty array
     */
    function to_array(mixed $input): array
    {
        if (is_array($input)) {
            return $input;
        }

        if ($input instanceof ArrayInterface) {
            return $input->toArray();
        }

        if ($input instanceof Traversable) {
            return iterator_to_array($input);
        }

        if ($input instanceof Countable) {
            $count = count($input);
            $result = [];

            for ($i = 0; $i < $count; $i++) {
                if (isset($input[$i])) {
                    $result[$i] = $input[$i];
                }
            }

            return $result;
        }

        // Scalar values (string, int, float, bool) and objects are wrapped
        if (is_scalar($input) || is_object($input)) {
            return [$input];
        }

        return [];
    }
}
