<?php

declare(strict_types=1);

namespace Tempest\Support\Regex {
    use Closure;
    use RuntimeException;

    use function Tempest\Support\Str\starts_with;
    use function Tempest\Support\Str\strip_start;

    /**
     * Returns all portions of the `$subject` that match the given `$pattern`.
     *
     * @param non-empty-string $pattern The pattern to match against.
     */
    function get_all_matches(string $subject, string $pattern, int $flags = 0, int $offset = 0): array
    {
        return call_preg('preg_match_all', static function () use ($subject, $pattern, $flags, $offset): array {
            $matches = [];
            $result = preg_match_all(
                $pattern,
                $subject,
                $matches,
                $flags,
                $offset,
            );

            if ($result === false || $result === 0) {
                return [];
            }

            return $matches;
        });
    }

    /**
     * Returns the first match of `$pattern` in `$subject`.
     *
     * @param non-empty-string $pattern The pattern to match against.
     * @param 0|256|512|768 $flags
     */
    function get_first_match(string $subject, string $pattern, int $flags = 0, int $offset = 0): array
    {
        return call_preg('preg_match', static function () use ($subject, $pattern, $flags, $offset): array {
            $matches = [];
            $result = preg_match(
                $pattern,
                $subject,
                $matches,
                $flags,
                $offset,
            );

            if ($result === false) {
                return [];
            }

            return $matches;
        });
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
    function replace(array|string $haystack, array|string $pattern, callable|array|string $replacement, ?int $limit = null): string
    {
        if (is_callable($replacement)) {
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
