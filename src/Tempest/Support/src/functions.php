<?php

declare(strict_types=1);

namespace Tempest\Support {
    use Closure;
    use Stringable;
    use Tempest\Support\Arr\ImmutableArray;
    use Tempest\Support\Str\ImmutableString;

    /**
     * Creates an instance of {@see \Tempest\Support\Str\ImmutableString} using the given `$string`.
     */
    function str(Stringable|int|string|null $string = ''): ImmutableString
    {
        return new ImmutableString($string);
    }

    /**
     * Creates an instance of {@see \Tempest\Support\Arr\ImmutableCollection} using the given `$input`. If `$input` is not an array, it will be wrapped in one.
     */
    function arr(mixed $input = []): ImmutableArray
    {
        return new ImmutableArray($input);
    }

    /**
     * Creates and sanitizes a file system path from the given `$parts`. The resulting path is not checked against the file system.
     */
    function path(Stringable|string ...$parts): Path
    {
        return new Path(...$parts);
    }

    /**
     * Executes the callback with the given `$value` and returns the same `$value`.
     *
     * @template T
     *
     * @param T $value
     * @param (Closure(T): void) $callback
     *
     * @return T
     */
    function tap(mixed $value, Closure $callback): mixed
    {
        $callback($value);

        return $value;
    }
}
