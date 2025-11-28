<?php

declare(strict_types=1);

namespace Tempest\Support {
    use Closure;
    use Stringable;
    use Tempest\Support\Arr\ImmutableArray;
    use Tempest\Support\Path\Path;
    use Tempest\Support\Str\ImmutableString;

    /**
     * Creates an instance of {@see \Tempest\Support\Str\ImmutableString} using the given `$string`.
     */
    function str(Stringable|int|string|null $string = ''): ImmutableString
    {
        return new ImmutableString($string);
    }

    /**
     * Creates an instance of {@see \Tempest\Support\Arr\ImmutableArray} using the given `$input`. If `$input` is not an array, it will be wrapped in one.
     */
    function arr(mixed $input = []): ImmutableArray
    {
        return new ImmutableArray($input);
    }

    /**
     * Normalizes the given path without checking it against the filesystem.
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
     * @param (callable(T): void) $callback
     *
     * @return T
     */
    function tap(mixed $value, callable $callback): mixed
    {
        $callback($value);

        return $value;
    }

    /**
     * Returns a tuple containing the result of the `$callback` as the first element and the error message as the second element if there was an error.
     *
     * @template T
     *
     * @param (Closure(): T) $callback
     *
     * @return array{0: T, 1: ?string}
     */
    function box(Closure $callback): array
    {
        $lastMessage = null;

        set_error_handler(static function (int $_type, string $message) use (&$lastMessage): void { // @phpstan-ignore argument.type
            $lastMessage = $message;
        });

        if (null !== $lastMessage && Str\contains($lastMessage, '): ')) {
            $lastMessage = Str\after_first(Str\to_lower_case($lastMessage), '): ');
        }

        try {
            $value = $callback();

            return [$value, $lastMessage];
        } finally {
            restore_error_handler();
        }
    }
}
