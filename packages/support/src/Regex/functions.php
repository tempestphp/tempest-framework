<?php

declare(strict_types=1);

namespace Tempest\Support\Regex {
    use Closure;
    use RuntimeException;
    use Stringable;
    use Tempest\Support\Arr\ImmutableArray;

    use function Tempest\Support\arr;
    use function Tempest\Support\Arr\filter;
    use function Tempest\Support\Arr\first;
    use function Tempest\Support\Arr\get_by_key;
    use function Tempest\Support\Arr\wrap;
    use function Tempest\Support\Str\starts_with;
    use function Tempest\Support\Str\strip_end;
    use function Tempest\Support\Str\strip_start;

    /**
     * Returns portions of the `$subject` that match the given `$pattern`. If `$global` is set to `true`, returns all matches. Otherwise, only returns the first one.
     *
     * @param non-empty-string $pattern The pattern to match against.
     * @param 0|2|256|512|768 $flags
     */
    function get_matches(Stringable|string $subject, Stringable|string $pattern, bool $global = false, int $flags = 0, int $offset = 0): array
    {
        if (str_ends_with($pattern, 'g')) {
            $global = true;
            $pattern = strip_end($pattern, 'g');
        }

        return call_preg($global ? 'preg_match_all' : 'preg_match', static function () use ($subject, $pattern, $global, $flags, $offset): array {
            $matches = [];
            $result = match ($global) {
                true => preg_match_all(
                    (string) $pattern,
                    (string) $subject,
                    $matches,
                    $flags,
                    $offset,
                ),
                false => preg_match(
                    (string) $pattern,
                    (string) $subject,
                    $matches,
                    $flags,
                    $offset,
                ),
            };

            if ($result === false || $result === 0) {
                return [];
            }

            return $matches;
        });
    }

    /**
     * Returns the specified matches of `$pattern` in `$subject`.
     *
     * @param non-empty-string $pattern The pattern to match against.
     */
    function get_all_matches(
        Stringable|string $subject,
        Stringable|string $pattern,
        Stringable|string|int|array $matches = 0,
        int $offset = 0,
    ): array {
        $result = get_matches($subject, $pattern, true, PREG_SET_ORDER, $offset);

        return arr($result)
            ->map(fn (array $result) => filter($result, fn ($_, string|int $key) => in_array($key, wrap($matches), strict: false)))
            ->toArray();
    }

    /**
     * Returns the specified match of `$pattern` in `$subject`. If no match is specified, returns the whole matching array.
     *
     * @param non-empty-string $pattern The pattern to match against.
     * @param 0|256|512|768 $flags
     */
    function get_match(
        Stringable|string $subject,
        Stringable|string $pattern,
        null|array|Stringable|int|string $match = null,
        mixed $default = null,
        int $flags = 0,
        int $offset = 0,
    ): null|int|string|array {
        $result = get_matches($subject, $pattern, false, $flags, $offset);

        if ($match === null) {
            return $result;
        }

        if (is_array($match)) {
            return arr($result)
                ->filter(fn ($_, string|int $key) => in_array($key, $match, strict: false))
                ->mapWithKeys(fn (array $matches, string|int $key) => yield $key => first($matches))
                ->toArray();
        }

        return get_by_key($result, $match, $default);
    }

    /**
     * Determines if $subject matches the given $pattern.
     *
     * @param non-empty-string $pattern The pattern to match against.
     */
    function matches(string $subject, string $pattern, int $offset = 0): bool
    {
        return call_preg('preg_match', static fn (): int|false => preg_match($pattern, $subject, offset: $offset)) === 1;
    }

    /**
     * Returns the '$haystack' string with all occurrences of `$pattern` replaced by `$replacement`.
     *
     * @param non-empty-string $pattern The pattern to search for.
     * @param null|positive-int $limit The maximum possible replacements for $pattern within $haystack.
     */
    function replace(array|string $haystack, array|string $pattern, Closure|array|string $replacement, ?int $limit = null): string
    {
        if ($replacement instanceof Closure) {
            return (string) call_preg('preg_replace_callback', static fn (): ?string => preg_replace_callback(
                $pattern,
                $replacement,
                $haystack,
                $limit ?? -1,
            ));
        }

        return (string) call_preg('preg_replace', static fn (): ?string => preg_replace(
            $pattern,
            $replacement,
            $haystack,
            $limit ?? -1,
        ));
    }

    /**
     * Returns the '$haystack' string with all occurrences of the keys of
     * '$replacements' (patterns) replaced by the corresponding values.
     *
     * @param array<non-empty-string, string> $replacements An array where the keys are regular expression patterns, and the values are the replacements.
     * @param null|positive-int $limit The maximum possible replacements for each pattern in $haystack.
     */
    function replace_every(string $haystack, array $replacements, ?int $limit = null): string
    {
        return (string) call_preg('preg_replace', static fn (): ?string => preg_replace(
            array_keys($replacements),
            array_values($replacements),
            $haystack,
            $limit ?? -1,
        ));
    }

    /**
     * @return null|array{message: string, code: int, pattern_message: null|string}
     * @internal
     */
    function get_preg_error(string $function): ?array
    {
        $code = preg_last_error();
        if ($code === PREG_NO_ERROR) {
            return null;
        }

        $messages = [
            PREG_INTERNAL_ERROR => 'Internal error',
            PREG_BAD_UTF8_ERROR => 'Malformed UTF-8 characters, possibly incorrectly encoded',
            PREG_BAD_UTF8_OFFSET_ERROR => 'The offset did not correspond to the beginning of a valid UTF-8 code point',
            PREG_BACKTRACK_LIMIT_ERROR => 'Backtrack limit exhausted',
            PREG_RECURSION_LIMIT_ERROR => 'Recursion limit exhausted',
            PREG_JIT_STACKLIMIT_ERROR => 'JIT stack limit exhausted',
        ];

        $message = $messages[$code] ?? 'Unknown error';
        $result = ['message' => $message, 'code' => $code, 'pattern_message' => null];
        $error = error_get_last();

        if ($error !== null && starts_with($error['message'], $function)) {
            $result['pattern_message'] = strip_start($error['message'], sprintf('%s(): ', $function));
        }

        return $result;
    }

    /**
     * @template T
     *
     * @param non-empty-string $function
     * @param Closure(): T $closure
     *
     * @return T
     * @internal
     */
    function call_preg(string $function, Closure $closure): mixed
    {
        error_clear_last();
        $result = @$closure();

        if ($error = get_preg_error($function)) {
            if ($error['pattern_message'] !== null) {
                throw new InvalidPatternException($error['pattern_message'], $error['code']);
            }

            throw new RuntimeException($error['message'], $error['code']);
        }

        return $result;
    }
}
