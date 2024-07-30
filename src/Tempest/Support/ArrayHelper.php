<?php

declare(strict_types=1);

namespace Tempest\Support;

final readonly class ArrayHelper
{
    public static function get(array &$array, string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);

        foreach ($keys as $key) {
            if (! isset($array[$key])) {
                return $default;
            }

            $array = $array[$key];
        }

        return $array;
    }

    public static function has(array $array, string $key): bool
    {
        $keys = explode('.', $key);

        foreach ($keys as $key) {
            if (! isset($array[$key])) {
                return false;
            }

            $array = &$array[$key];
        }

        return true;
    }

    public static function set(array &$array, string $key, mixed $value): array
    {
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
            if (! isset($array[$key]) || ! is_array($array[$key])) {
                $array[$key] = [];
            }

            // Set the context to this key.
            $array = &$array[$key];
        }

        // Pull the first key out of the array
        // and use it to set the value.
        $array[array_shift($keys)] = $value;

        return $array;
    }

    /**
     * @template T
     * @param T[]|T $value
     *
     * @return array<T>
     */
    public static function wrap(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        return [$value];
    }

    public function unwrap(array $array): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $result = array_merge_recursive($result, $this->toArray($key, $value));
        }

        return $result;
    }

    public function toArray(string|int $key, mixed $value): array
    {
        if (is_int($key)) {
            return [$key => $value];
        }

        $keys = explode('.', $key);

        for ($i = array_key_last($keys); $i >= 0; $i--) {
            $currentKey = $keys[$i];

            $value = [$currentKey => $value];
        }

        return $value;
    }
}
