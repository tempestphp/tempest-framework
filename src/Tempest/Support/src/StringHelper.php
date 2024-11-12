<?php

declare(strict_types=1);

namespace Tempest\Support;

use Countable;
use function ltrim;
use function preg_quote;
use function preg_replace;
use function rtrim;
use Stringable;
use function trim;

final readonly class StringHelper implements Stringable
{
    public function __construct(
        private string $string = '',
    ) {
    }

    /**
     * Converts the instance to a string.
     */
    public function toString(): string
    {
        return $this->string;
    }

    /**
     * Converts the instance to a string.
     */
    public function __toString(): string
    {
        return $this->string;
    }

    /**
     * Asserts whether the instance is equal to the given instance or string.
     */
    public function equals(string|Stringable $other): bool
    {
        return $this->string === (string) $other;
    }

    /**
     * Converts the instance to title case.
     */
    public function title(): self
    {
        return new self(mb_convert_case($this->string, MB_CASE_TITLE, 'UTF-8'));
    }

    /**
     * Converts the instance to lower case.
     */
    public function lower(): self
    {
        return new self(mb_strtolower($this->string, 'UTF-8'));
    }

    /**
     * Converts the instance to upper case.
     */
    public function upper(): self
    {
        return new self(mb_strtoupper($this->string, 'UTF-8'));
    }

    /**
     * Converts the instance to snake case.
     */
    public function snake(string $delimiter = '_'): self
    {
        $string = $this->string;

        if (ctype_lower($string)) {
            return $this;
        }

        $string = preg_replace('/(.)(?=[A-Z])/u', '$1' . $delimiter, $string);
        $string = preg_replace(
            '![^' . preg_quote($delimiter) . '\pL\pN\s]+!u',
            $delimiter,
            mb_strtolower($string, 'UTF-8')
        );
        $string = preg_replace('/\s+/u', $delimiter, $string);
        $string = trim($string, $delimiter);

        return (new self($string))->deduplicate($delimiter);
    }

    /**
     * Converts the instance to kebab case.
     */
    public function kebab(): self
    {
        return $this->snake('-');
    }

    /**
     * Converts the instance to pascal case.
     */
    public function pascal(): self
    {
        $words = explode(' ', str_replace(['-', '_'], ' ', $this->string));

        // TODO: use `mb_ucfirst` when it has landed in PHP 8.4
        $studlyWords = array_map(static fn (string $word) => ucfirst($word), $words);

        return new self(implode('', $studlyWords));
    }

    /**
     * Converts the instance to camel case.
     */
    public function camel(): self
    {
        return new self(lcfirst((string)$this->pascal()));
    }

    /**
     * Replaces consecutive instances of a given character with a single character.
     */
    public function deduplicate(string|array $characters = ' '): self
    {
        $string = $this->string;

        foreach (arr($characters) as $character) {
            $string = preg_replace('/' . preg_quote($character, '/') . '+/u', $character, $string);
        }

        return new self($string);
    }

    /**
     * Converts the instance to its English plural form.
     */
    public function pluralize(int|array|Countable $count = 2): self
    {
        return new self(LanguageHelper::pluralize($this->string, $count));
    }

    /**
     * Converts the last word to its English plural form.
     */
    public function pluralizeLast(int|array|Countable $count = 2): self
    {
        $parts = preg_split('/(.)(?=[A-Z])/u', $this->string, -1, PREG_SPLIT_DELIM_CAPTURE);

        $lastWord = array_pop($parts);

        $string = implode('', $parts) . (new self($lastWord))->pluralize($count);

        return new self($string);
    }

    /**
     * Creates a random alpha-numeric string of the given length.
     */
    public function random(int $length = 16): self
    {
        $string = '';

        while (($len = strlen($string)) < $length) {
            $size = $length - $len;
            $bytesSize = (int)ceil($size / 3) * 3;
            $bytes = random_bytes($bytesSize);
            $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), offset: 0, length: $size);
        }

        return new self($string);
    }

    /**
     * Caps the instance with the given string.
     */
    public function finish(string $cap): self
    {
        return new self(
            preg_replace('/(?:' . preg_quote($cap, '/') . ')+$/u', replacement: '', subject: $this->string) . $cap
        );
    }

    /**
     * Prefixes the instance with the given string.
     */
    public function start(string $prefix): self
    {
        return new self(
            $prefix.preg_replace('/^(?:'.preg_quote($prefix, '/').')+/u', replacement: '', subject: $this->string)
        );
    }

    /**
     * Returns the remainder of the string after the first occurrence of the given value.
     */
    public function after(Stringable|string|array $search): self
    {
        $search = $this->normalizeString($search);

        if ($search === '' || $search === []) {
            return $this;
        }

        $nearestPosition = mb_strlen($this->string); // Initialize with a large value
        $foundSearch = '';

        foreach (arr($search) as $term) {
            $position = mb_strpos($this->string, $term);

            if ($position !== false && $position < $nearestPosition) {
                $nearestPosition = $position;
                $foundSearch = $term;
            }
        }

        if ($nearestPosition === mb_strlen($this->string)) {
            return $this;
        }

        $string = mb_substr($this->string, $nearestPosition + mb_strlen($foundSearch));

        return new self($string);
    }

    /**
     * Returns the remainder of the string after the last occurrence of the given value.
     */
    public function afterLast(Stringable|string|array $search): self
    {
        $search = $this->normalizeString($search);

        if ($search === '' || $search === []) {
            return $this;
        }

        $farthestPosition = -1;
        $foundSearch = null;

        foreach (arr($search) as $term) {
            $position = mb_strrpos($this->string, $term);

            if ($position !== false && $position > $farthestPosition) {
                $farthestPosition = $position;
                $foundSearch = $term;
            }
        }

        if ($farthestPosition === -1 || $foundSearch === null) {
            return $this;
        }

        $string = mb_substr($this->string, $farthestPosition + strlen($foundSearch));

        return new self($string);
    }

    /**
     * Returns the portion of the string before the first occurrence of the given value.
     */
    public function before(Stringable|string|array $search): self
    {
        $search = $this->normalizeString($search);

        if ($search === '' || $search === []) {
            return $this;
        }

        $nearestPosition = mb_strlen($this->string);

        foreach (arr($search) as $char) {
            $position = mb_strpos($this->string, $char);

            if ($position !== false && $position < $nearestPosition) {
                $nearestPosition = $position;
            }
        }

        if ($nearestPosition === mb_strlen($this->string)) {
            return $this;
        }

        $string = mb_substr($this->string, start: 0, length: $nearestPosition);

        return new self($string);
    }

    /**
     * Returns the portion of the string before the last occurrence of the given value.
     */
    public function beforeLast(Stringable|string|array $search): self
    {
        $search = $this->normalizeString($search);

        if ($search === '' || $search === []) {
            return $this;
        }

        $farthestPosition = -1;

        foreach (arr($search) as $char) {
            $position = mb_strrpos($this->string, $char);

            if ($position !== false && $position > $farthestPosition) {
                $farthestPosition = $position;
            }
        }

        if ($farthestPosition === -1) {
            return $this;
        }

        $string = mb_substr($this->string, start: 0, length: $farthestPosition);

        return new self($string);
    }

    /**
     * Returns the portion of the string between the widest possible instances of the given strings.
     */
    public function between(string|Stringable $from, string|Stringable $to): self
    {
        $from = $this->normalizeString($from);
        $to = $this->normalizeString($to);

        if ($from === '' || $to === '') {
            return $this;
        }

        return $this->after($from)->beforeLast($to);
    }

    /**
     * Removes all whitespace (or specified characters) from both ends of the instance.
     */
    public function trim(string $characters = " \n\r\t\v\0"): self
    {
        return new self(trim($this->string, $characters));
    }

    /**
     * Removes all whitespace (or specified characters) from the start of the instance.
     */
    public function ltrim(string $characters = " \n\r\t\v\0"): self
    {
        return new self(ltrim($this->string, $characters));
    }

    /**
     * Removes all whitespace (or specified characters) from the end of the instance.
     */
    public function rtrim(string $characters = " \n\r\t\v\0"): self
    {
        return new self(rtrim($this->string, $characters));
    }

    /**
     * Returns the multi-bytes length of the instance.
     */
    public function length(): int
    {
        return mb_strlen($this->string);
    }

    /**
     * Returns the base name of the instance, assuming the instance is a class name.
     */
    public function classBasename(): self
    {
        return new self(basename(str_replace('\\', '/', $this->string)));
    }

    /**
     * Asserts whether the instance starts with one of the given needles.
     */
    public function startsWith(Stringable|string|array $needles): bool
    {
        if (! is_array($needles)) {
            $needles = [$needles];
        }

        foreach ($needles as $needle) {
            if (str_starts_with($this->string, (string) $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Asserts whether the instance ends with one of the given `$needles`.
     */
    public function endsWith(Stringable|string|array $needles): bool
    {
        if (! is_array($needles)) {
            $needles = [$needles];
        }

        foreach ($needles as $needle) {
            if (str_ends_with($this->string, (string) $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Replaces the first occurence of `$search` with `$replace`.
     */
    public function replaceFirst(Stringable|string $search, Stringable|string $replace): self
    {
        $search = $this->normalizeString($search);

        if ($search === '') {
            return $this;
        }

        $position = strpos($this->string, (string) $search);

        if ($position === false) {
            return $this;
        }

        return new self(substr_replace($this->string, $replace, $position, strlen($search)));
    }

    /**
     * Replaces the last occurence of `$search` with `$replace`.
     */
    public function replaceLast(Stringable|string $search, Stringable|string $replace): self
    {
        $search = $this->normalizeString($search);

        if ($search === '') {
            return $this;
        }

        $position = strrpos($this->string, (string) $search);

        if ($position === false) {
            return $this;
        }

        return new self(substr_replace($this->string, $replace, $position, strlen($search)));
    }

    /**
     * Replaces `$search` with `$replace` if `$search` is at the end of the instance.
     */
    public function replaceEnd(Stringable|string $search, Stringable|string $replace): self
    {
        $search = $this->normalizeString($search);

        if ($search === '') {
            return $this;
        }

        if (! $this->endsWith($search)) {
            return $this;
        }

        return $this->replaceLast($search, $replace);
    }

    /**
     * Replaces `$search` with `$replace` if `$search` is at the start of the instance.
     */
    public function replaceStart(Stringable|string $search, Stringable|string $replace): self
    {
        if ($search === '') {
            return $this;
        }

        if (! $this->startsWith($search)) {
            return $this;
        }

        return $this->replaceFirst($search, $replace);
    }

    /**
     * Appends the given strings to the instance.
     */
    public function append(string|Stringable ...$append): self
    {
        return new self($this->string . implode('', $append));
    }

    /**
     * Prepends the given strings to the instance.
     */
    public function prepend(string|Stringable ...$prepend): self
    {
        return new self(implode('', $prepend) . $this->string);
    }

    /**
     * Wraps the instance with the given string. If `$after` is specified, it will be appended instead of `$before`.
     *
     * ### Example
     * ```php
     * str('Scott')->wrap(before: 'Leon ', after: ' Kennedy'); // Leon Scott Kennedy
     * ```
     */
    public function wrap(string|Stringable $before, string|Stringable $after = null): self
    {
        return new self($before . $this->string . ($after ??= $before));
    }

    /**
     * Removes the specified `$before` and `$after` from the beginning and the end of the instance. If `$after` is null, `$before` is used instead.
     * Setting `$strict` to `false` will unwrap the instance even if both ends do not correspond to the specified `$before` and `$after`.
     *
     * ### Example
     * ```php
     *  str('Scott Kennedy')->unwrap(before: 'Leon ', after: ' Kennedy', strict: false); // Scott
     * ```
     */
    public function unwrap(string|Stringable $before, string|Stringable $after = null, bool $strict = true): self
    {
        $string = $this->string;

        if ($string === '') {
            return $this;
        }

        if ($after === null) {
            $after = $before;
        }

        if (! $strict) {
            return (new self($string))->after($before)->beforeLast($after);
        }

        if ($this->startsWith($before) && $this->endsWith($after)) {
            $string = (string) (new self($string))->after($before)->beforeLast($after);
        }

        return new self($string);
    }

    /**
     * Replaces all occurrences of the given `$search` with `$replace`.
     */
    public function replace(Stringable|string|array $search, Stringable|string|array $replace): self
    {
        $search = $this->normalizeString($search);
        $replace = $this->normalizeString($replace);

        return new self(str_replace($search, $replace, $this->string));
    }

    /**
     * Replaces the patterns matching the given regular expression.
     */
    public function replaceRegex(string|array $regex, string|array|callable $replace): self
    {
        if (is_callable($replace)) {
            return new self(preg_replace_callback($regex, $replace, $this->string));
        }

        return new self(preg_replace($regex, $replace, $this->string));
    }

    /**
     * Gets the first portion of the instance that matches the given regular expression.
     */
    public function match(string $regex): array
    {
        preg_match($regex, $this->string, $matches);

        return $matches;
    }

    /**
     * Gets all portions of the instance that match the given regular expression.
     */
    public function matchAll(string $regex, int $flags = 0, int $offset = 0): array
    {
        $result = preg_match_all($regex, $this->string, $matches, $flags, $offset);

        if ($result === 0) {
            return [];
        }

        return $matches;
    }

    /**
     * Asserts whether the instance matches the given regular expression.
     */
    public function matches(string $regex): bool
    {
        return ($this->match($regex)[0] ?? null) !== null;
    }

    /**
     * Dumps the instance and stops the execution of the script.
     */
    public function dd(mixed ...$dd): void
    {
        ld($this->string, ...$dd);
    }

    /**
     * Dumps the instance.
     */
    public function dump(mixed ...$dumps): self
    {
        lw($this->string, ...$dumps);

        return $this;
    }

    /**
     * Extracts an excerpt from the instance.
     */
    public function excerpt(int $from, int $to, bool $asArray = false): self|ArrayHelper
    {
        $lines = explode(PHP_EOL, $this->string);

        $from = max(0, $from - 1);

        $to = min($to - 1, count($lines));

        $lines = array_slice($lines, $from, $to - $from + 1, true);

        if ($asArray) {
            return arr($lines)
                ->mapWithKeys(fn (string $line, int $number) => yield $number + 1 => $line);
        }

        return new self(implode(PHP_EOL, $lines));
    }

    private function normalizeString(mixed $value): mixed
    {
        if ($value instanceof Stringable) {
            return (string) $value;
        }

        return $value;
    }

    /**
     * Explodes the string into an `ArrayHelper` instance by a separator.
     */
    public function explode(string $separator = ' '): ArrayHelper
    {
        return ArrayHelper::explode($this->string, $separator);
    }

    /**
     * Implodes the given array into a string by a separator.
     */
    public static function implode(array|ArrayHelper $parts, string $glue = ' '): self
    {
        return arr($parts)->implode($glue);
    }

    /**
     * Joins all values using the specified `$glue`. The last item of the string is separated by `$finalGlue`.
     */
    public static function join(array|ArrayHelper $parts, string $glue = ', ', ?string $finalGlue = ' and '): self
    {
        return arr($parts)->join($glue, $finalGlue);
    }
}
