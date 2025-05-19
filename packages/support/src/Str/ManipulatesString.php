<?php

declare(strict_types=1);

namespace Tempest\Support\Str;

use ArrayAccess;
use Closure;
use Countable;
use Stringable;
use Tempest\Support\Arr\ImmutableArray;
use Tempest\Support\Regex;

use function Tempest\Support\arr;
use function Tempest\Support\Random\secure_string;
use function Tempest\Support\tap;

/**
 * @internal
 */
trait ManipulatesString
{
    private(set) string $value;

    public function __construct(Stringable|int|string|null $string = '')
    {
        $this->value = (string) ($string ?? '');
    }

    /**
     * Returns a new instance with the specified string,
     * or mutates the instance if this is a `MutableString`.
     */
    abstract protected function createOrModify(Stringable|string $string): self;

    /**
     * Prefixes the instance with the given string.
     */
    public function start(Stringable|string $prefix): self
    {
        return $this->createOrModify(ensure_starts_with($this->value, $prefix));
    }

    /**
     * Caps the string with the given string.
     */
    public function finish(Stringable|string $cap): self
    {
        return $this->createOrModify(ensure_ends_with($this->value, $cap));
    }

    /**
     * Returns the remainder of the string after the first occurrence of the given value.
     */
    public function afterFirst(Stringable|string|array $search): self
    {
        return $this->createOrModify(after_first($this->value, $search));
    }

    /**
     * Returns the remainder of the string after the last occurrence of the given value.
     */
    public function afterLast(Stringable|string|array $search): self
    {
        return $this->createOrModify(after_last($this->value, $search));
    }

    /**
     * Returns the portion of the string before the first occurrence of the given value.
     */
    public function before(Stringable|string|array $search): self
    {
        return $this->createOrModify(before_first($this->value, $search));
    }

    /**
     * Returns the portion of the string before the last occurrence of the given value.
     */
    public function beforeLast(Stringable|string|array $search): self
    {
        return $this->createOrModify(before_last($this->value, $search));
    }

    /**
     * Returns the portion of the string between the widest possible instances of the given strings.
     */
    public function between(string|Stringable $from, string|Stringable $to): self
    {
        return $this->createOrModify(between($this->value, $from, $to));
    }

    /**
     * Removes all whitespace (or specified characters) from both ends of the instance.
     */
    public function trim(string $characters = " \n\r\t\v\0"): self
    {
        return $this->createOrModify(trim($this->value, $characters));
    }

    /**
     * Removes all whitespace (or specified characters) from the start of the instance.
     */
    public function ltrim(string $characters = " \n\r\t\v\0"): self
    {
        return $this->createOrModify(ltrim($this->value, $characters));
    }

    /**
     * Removes all whitespace (or specified characters) from the end of the instance.
     */
    public function rtrim(string $characters = " \n\r\t\v\0"): self
    {
        return $this->createOrModify(rtrim($this->value, $characters));
    }

    /**
     * Converts the string to its English plural form.
     */
    public function pluralize(int|array|Countable $count = 2): self
    {
        return $this->createOrModify(pluralize($this->value, $count));
    }

    /**
     * Converts the last word to its English plural form.
     */
    public function pluralizeLastWord(int|array|Countable $count = 2): self
    {
        return $this->createOrModify(pluralize_last_word($this->value, $count));
    }

    /**
     * Converts the last word to its English plural form.
     */
    public function singularizeLastWord(): self
    {
        return $this->createOrModify(singularize_last_word($this->value));
    }

    /**
     * Creates a pseudo-random alpha-numeric string of the given length.
     */
    public function random(int $length = 16): self
    {
        return $this->createOrModify(secure_string($length));
    }

    /**
     * Converts the string to title case.
     */
    public function title(): self
    {
        return $this->createOrModify(to_title_case($this->value));
    }

    /**
     * Converts the instance to snake case.
     */
    public function snake(Stringable|string $delimiter = '_'): self
    {
        return $this->createOrModify(to_snake_case($this->value, $delimiter));
    }

    /**
     * Converts the instance to kebab case.
     */
    public function kebab(): self
    {
        return $this->createOrModify(to_kebab_case($this->value));
    }

    /**
     * Converts the instance to pascal case.
     */
    public function pascal(): self
    {
        return $this->createOrModify(to_pascal_case($this->value));
    }

    /**
     * Converts the instance to camel case.
     */
    public function camel(): self
    {
        return $this->createOrModify(to_camel_case($this->value));
    }

    /**
     * Converts the current string to an URL-safe slug.
     *
     * @param bool $replaceSymbols Adds some more replacements e.g. "£" with "pound".
     */
    public function slug(Stringable|string $separator = '-', array $replacements = [], bool $replaceSymbols = true): self
    {
        return $this->createOrModify(to_slug($this->value, $separator, $replacements, $replaceSymbols));
    }

    /**
     * Converts the current string to a naive sentence case.
     */
    public function sentence(): self
    {
        return $this->createOrModify(to_sentence_case($this->value));
    }

    /**
     * Returns an array of words from the current string.
     */
    public function words(): ImmutableArray
    {
        return new ImmutableArray(to_words($this->value));
    }

    /**
     * Transliterates the current string to ASCII. Invalid characters are replaced with their closest counterpart.
     *
     * @param string $language Language of the source string. Defaults to english.
     */
    public function ascii(Stringable|string $language = 'en'): self
    {
        return $this->createOrModify(to_ascii($this->value, $language));
    }

    /**
     * Asserts whether the instance is an ASCII string.
     */
    public function isAscii(): bool
    {
        return is_ascii($this->value);
    }

    /**
     * Replaces consecutive instances of a given character with a single character.
     */
    public function deduplicate(Stringable|string|iterable $characters = ' '): self
    {
        return $this->createOrModify(deduplicate($this->value, $characters));
    }

    /**
     * Converts the instance to lower case.
     */
    public function lower(): self
    {
        return $this->createOrModify(to_lower_case($this->value));
    }

    /**
     * Converts the instance to upper case.
     */
    public function upper(): self
    {
        return $this->createOrModify(to_upper_case($this->value));
    }

    /**
     * Changes the case of the first letter to uppercase.
     */
    public function upperFirst(): self
    {
        return $this->createOrModify(upper_first($this->value));
    }

    /**
     * Changes the case of the first letter to lowercase.
     */
    public function lowerFirst(): self
    {
        return $this->createOrModify(lower_first($this->value));
    }

    /**
     * Keeps only the base name of the instance.
     */
    public function basename(string $suffix = ''): self
    {
        return $this->createOrModify(basename($this->value, $suffix));
    }

    /**
     * Keeps only the base name of the instance, assuming the instance is a class name.
     */
    public function classBasename(): self
    {
        return $this->createOrModify(class_basename($this->value));
    }

    /**
     * Replaces the first occurrence of `$search` with `$replace`.
     */
    public function replaceFirst(array|Stringable|string $search, Stringable|string $replace): self
    {
        return $this->createOrModify(replace_first($this->value, $search, $replace));
    }

    /**
     * Replaces the last occurrence of `$search` with `$replace`.
     */
    public function replaceLast(array|Stringable|string $search, Stringable|string $replace): self
    {
        return $this->createOrModify(replace_last($this->value, $search, $replace));
    }

    /**
     * Replaces `$search` with `$replace` if `$search` is at the end of the instance.
     */
    public function replaceEnd(array|Stringable|string $search, Stringable|string $replace): self
    {
        return $this->createOrModify(replace_end($this->value, $search, $replace));
    }

    /**
     * Replaces `$search` with `$replace` if `$search` is at the start of the instance.
     */
    public function replaceStart(array|Stringable|string $search, Stringable|string $replace): self
    {
        return $this->createOrModify(replace_start($this->value, $search, $replace));
    }

    /**
     * Replaces all occurrences of the keys of `$replacements` with the corresponding values.
     *
     * @param array<string,Stringable|string> $replacements
     */
    public function replaceEvery(array $replacements): self
    {
        $haystack = $this->value;

        foreach ($replacements as $needle => $replacement) {
            $haystack = namespace\replace($this->value, $needle, (string) $replacement);
        }

        return $this->createOrModify($haystack);
    }

    /**
     * Strips the specified `$prefix` from the start of the string.
     */
    public function stripStart(array|Stringable|string $prefix): self
    {
        return $this->createOrModify(strip_start($this->value, $prefix));
    }

    /**
     * Strips the specified `$suffix` from the end of the string.
     */
    public function stripEnd(array|Stringable|string $suffix): self
    {
        return $this->createOrModify(strip_end($this->value, $suffix));
    }

    /**
     * Replaces the portion of the specified `$length` at the specified `$position` with the specified `$replace`.
     *
     * ### Example
     * ```php
     * str('Lorem dolor')->replaceAt(6, 5, 'ipsum'); // Lorem ipsum
     * ```
     */
    public function replaceAt(int $position, int $length, Stringable|string $replace): self
    {
        return $this->createOrModify(replace_at($this->value, $position, $length, $replace));
    }

    /**
     * Appends the given strings to the instance.
     */
    public function append(string|Stringable ...$append): self
    {
        return $this->createOrModify(append($this->value, ...$append));
    }

    /**
     * Prepends the given strings to the instance.
     */
    public function prepend(string|Stringable ...$prepend): self
    {
        return $this->createOrModify(prepend($this->value, ...$prepend));
    }

    /**
     * Wraps the instance with the given string. If `$after` is specified, it will be appended instead of `$before`.
     *
     * ### Example
     * ```php
     * str('Scott')->wrap(before: 'Leon ', after: ' Kennedy'); // Leon Scott Kennedy
     * ```
     */
    public function wrap(string|Stringable $before, string|Stringable|null $after = null): self
    {
        return $this->createOrModify(wrap($this->value, $before, $after));
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
    public function unwrap(string|Stringable $before, string|Stringable|null $after = null, bool $strict = true): self
    {
        return $this->createOrModify(unwrap($this->value, $before, $after, $strict));
    }

    /**
     * Extracts an excerpt from the instance.
     */
    public function excerpt(int $from, int $to, bool $asArray = false): self|ImmutableArray
    {
        $value = excerpt($this->value, $from, $to, $asArray);

        if ($asArray) {
            return new ImmutableArray($value);
        }

        return $this->createOrModify($value);
    }

    /**
     * Truncates the instance to the specified amount of characters.
     *
     * ### Example
     * ```php
     * str('Lorem ipsum')->truncate(5, end: '...'); // Lorem...
     * ```
     */
    public function truncate(int $characters, Stringable|string $end = ''): self
    {
        return $this->createOrModify(truncate_end($this->value, $characters, $end));
    }

    /**
     * Truncates the instance to the specified amount of characters from the start.
     *
     * ### Example
     * ```php
     * str('Lorem ipsum')->truncateStart(5, start: '...'); // ...ipsum
     * ```
     */
    public function truncateStart(int $characters, string $start = ''): self
    {
        return $this->createOrModify(truncate_start($this->value, $characters, $start));
    }

    /**
     * Reverses the instance.
     *
     * ### Example
     * ```php
     * str('Lorem ipsum')->reverse(); // muspi meroL
     * ```
     */
    public function reverse(): self
    {
        return $this->createOrModify(reverse($this->value));
    }

    /**
     * Gets parts of the instance.
     *
     * ### Example
     * ```php
     * str('Lorem ipsum')->substr(0, length: 5); // Lorem
     * str('Lorem ipsum')->substr(6); // ipsum
     * ```
     */
    public function substr(int $start, ?int $length = null): self
    {
        return $this->createOrModify(mb_substr($this->value, $start, $length));
    }

    /**
     * Takes the specified amount of characters. If `$length` is negative, starts from the end.
     */
    public function take(int $length): self
    {
        return $this->createOrModify(take($this->value, $length));
    }

    /**
     * Strips HTML and PHP tags from the instance.
     *
     * @param null|string|string[] $allowed Allowed tags.
     *
     * ### Example
     * ```php
     * str('<p>Lorem ipsum</p>')->stripTags(); // Lorem ipsum
     * str('<p>Lorem <strong>ipsum</strong></p>')->stripTags(allowed: 'strong'); // Lorem <strong>ipsum</strong>
     * ```
     */
    public function stripTags(null|string|array $allowed = null): self
    {
        return $this->createOrModify(strip_tags($this->value, $allowed));
    }

    /**
     * Pads the instance to the given `$width` and centers the text in it.
     *
     * ### Example
     * ```php
     * str('Lorem ipsum')->alignCenter(width: 20);
     * ```
     */
    public function alignCenter(?int $width, int $padding = 0): self
    {
        return $this->createOrModify(align_center($this->value, $width, $padding));
    }

    /**
     * Pads the instance to the given `$width` and aligns the text to the right.
     *
     * ### Example
     * ```php
     * str('Lorem ipsum')->alignRight(width: 20);
     * ```
     */
    public function alignRight(?int $width, int $padding = 0): self
    {
        return $this->createOrModify(align_right($this->value, $width, $padding));
    }

    /**
     * Pads the instance to the given `$width` and aligns the text to the left.
     *
     * ### Example
     * ```php
     * str('Lorem ipsum')->alignLeft(width: 20);
     * ```
     */
    public function alignLeft(?int $width, int $padding = 0): self
    {
        return $this->createOrModify(align_left($this->value, $width, $padding));
    }

    /**
     * Inserts the specified `$string` at the specified `$position`.
     *
     * ### Example
     * ```php
     * str('Lorem ipsum sit amet')->insertAt(11, ' dolor'); // Lorem ipsum dolor sit amet
     * ```
     */
    public function insertAt(int $position, string $string): self
    {
        return $this->createOrModify(insert_at($this->value, $position, $string));
    }

    /**
     * Returns the string padded to the total length by appending the `$pad_string` to the left.
     *
     * If the length of the input string plus the pad string exceeds the total
     * length, the pad string will be truncated. If the total length is less than or
     * equal to the length of the input string, no padding will occur.
     *
     * Example:
     *      pad_left('Ay', 4)
     *      => '  Ay'
     *
     *      pad_left('ay', 3, 'A')
     *      => 'Aay'
     *
     *      pad_left('eet', 4, 'Yeeeee')
     *      => 'Yeet'
     *
     *      pad_left('مرحبا', 8, 'م')
     *      => 'ممممرحبا'
     *
     * @param non-empty-string $padString
     * @param int<0, max> $totalLength
     */
    public function padLeft(int $totalLength, string $padString = ' '): self
    {
        return $this->createOrModify(namespace\pad_left($this->value, $totalLength, $padString));
    }

    /**
     * Returns the string padded to the total length by appending the `$pad_string` to the right.
     *
     * If the length of the input string plus the pad string exceeds the total
     * length, the pad string will be truncated. If the total length is less than or
     * equal to the length of the input string, no padding will occur.
     *
     * Example:
     *      pad_right('Ay', 4)
     *      => 'Ay  '
     *
     *      pad_right('Ay', 5, 'y')
     *      => 'Ayyyy'
     *
     *      pad_right('Yee', 4, 't')
     *      => 'Yeet'
     *
     *      pad_right('مرحبا', 8, 'ا')
     *      => 'مرحباااا'
     *
     * @param non-empty-string $padString
     * @param int<0, max> $totalLength
     */
    public function padRight(int $totalLength, string $padString = ' '): self
    {
        return $this->createOrModify(namespace\pad_right($this->value, $totalLength, $padString));
    }

    /**
     * Chunks the instance into parts of the specified `$length`.
     */
    public function chunk(int $length): ImmutableArray
    {
        return new ImmutableArray(chunk($this->value, $length));
    }

    /**
     * Explodes the string into an {@see \Tempest\Support\Arr\ImmutableArray} instance by a separator.
     */
    public function explode(string $separator = ' '): ImmutableArray
    {
        return new ImmutableArray(explode($separator, $this->value));
    }

    /**
     * Formats the string.
     */
    public function format(mixed ...$args): self
    {
        return $this->createOrModify(vsprintf($this->value, $args));
    }

    /**
     * Replaces all occurrences of the given `$search` with `$replace`.
     */
    public function replace(Stringable|string|array $search, Stringable|string|array $replace): self
    {
        return $this->createOrModify(replace($this->value, $search, $replace));
    }

    /**
     * Removes all occurrences of the given `$search`.
     */
    public function erase(Stringable|string|array $search): self
    {
        return $this->createOrModify(replace($this->value, $search, ''));
    }

    /**
     * Replaces the patterns matching the given regular expression.
     */
    public function replaceRegex(array|string $regex, array|string|callable $replace): self
    {
        return $this->createOrModify(Regex\replace($this->value, $regex, $replace));
    }

    /**
     * Gets the first portion of the instance that matches the given regular expression.
     *
     * ### Example
     * ```php
     * str('10-abc')->match('/(?<id>\d+-)/', match: 'id'); // 10
     * ```
     *
     * @param non-empty-string $pattern The regular expression to match on
     * @param string|int $match The group number or name to retrieve
     * @param mixed $default The default value to return if no match is found
     * @param 0|256|512|768 $flags
     */
    public function match(string $pattern, array|Stringable|int|string $match = 1, mixed $default = null, int $flags = 0, int $offset = 0): null|int|string|array
    {
        return Regex\get_match($this->value, $pattern, $match, $default, $flags, $offset);
    }

    /**
     * Gets all portions of the instance that match the given regular expression.
     *
     * @param non-empty-string $pattern The regular expression to match on
     */
    public function matchAll(Stringable|string $pattern, array|Stringable|int|string $matches = 0, int $offset = 0): ImmutableArray
    {
        return new ImmutableArray(Regex\get_all_matches($this->value, $pattern, $matches, $offset));
    }

    /**
     * Asserts whether the instance matches the given regular expression.
     *
     * ### Example
     * ```php
     * str('Lorem ipsum')->matches('/ipsum/'); // true
     * ```
     */
    public function matches(string $regex): bool
    {
        return Regex\matches($this->value, $regex);
    }

    /**
     * Checks whether this string contains another string
     *
     * ### Example
     * ```php
     * str('Lorem ipsum')->contains('ipsum'); // true
     * str('Lorem ipsum')->contains('something else'); // false
     * ```
     */
    public function contains(Stringable|string|array $needle): bool
    {
        return contains($this->value, $needle);
    }

    /**
     * Asserts whether the instance starts with one of the given needles.
     */
    public function startsWith(Stringable|string|array $needles): bool
    {
        return starts_with($this->value, $needles);
    }

    /**
     * Asserts whether the instance ends with one of the given `$needles`.
     */
    public function endsWith(Stringable|string|array $needles): bool
    {
        return ends_with($this->value, $needles);
    }

    /**
     * Calculates the levenshtein difference between this instance and the specified string.
     */
    public function levenshtein(Stringable|string $string): int
    {
        return levenshtein($this->value, $string);
    }

    /**
     * Implodes the given array into a string by a separator.
     */
    public static function implode(ArrayAccess|array $parts, string $glue = ' '): self
    {
        return new static(arr($parts)->implode($glue));
    }

    /**
     * Joins all values using the specified `$glue`. The last item of the string is separated by `$finalGlue`.
     */
    public static function join(ArrayAccess|array $parts, string $glue = ', ', ?string $finalGlue = ' and '): self
    {
        return new static(arr($parts)->join($glue, $finalGlue));
    }

    /**
     * Check whether the string is not empty.
     */
    public function isNotEmpty(): bool
    {
        return ! $this->isEmpty();
    }

    /**
     * Check whether the string is empty.
     */
    public function isEmpty(): bool
    {
        return is_empty($this->value);
    }

    /**
     * Asserts whether the instance is equal to the given instance or string.
     */
    public function equals(string|Stringable $other): bool
    {
        return $this->value === ((string) $other);
    }

    /**
     * Returns the multi-bytes length of the instance.
     */
    public function length(): int
    {
        return mb_strlen($this->value);
    }

    /**
     * Executes callback with the given `$value` and returns the same `$value`.
     *
     * @param (Closure(static): void) $callback
     */
    public function tap(Closure $callback): self
    {
        tap($this, $callback);

        return $this;
    }

    /**
     * Dumps the instance and stops the execution of the script.
     */
    public function dd(mixed ...$dd): void
    {
        ld($this->value, ...$dd);
    }

    /**
     * Dumps the instance.
     */
    public function dump(mixed ...$dumps): self
    {
        lw($this->value, ...$dumps);

        return $this;
    }

    /**
     * Converts to a scalar string.
     */
    public function toString(): string
    {
        return $this->__toString();
    }

    /**
     * Converts to a JSON serializable string.
     */
    public function jsonSerialize(): string
    {
        return $this->__toString();
    }

    /**
     * Converts to a scalar string.
     */
    public function __toString(): string
    {
        return $this->value;
    }

    public static function __set_state(array $array): object
    {
        return new self($array['value']);
    }
}
